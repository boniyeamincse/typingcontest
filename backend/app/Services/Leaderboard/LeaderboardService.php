<?php

namespace App\Services\Leaderboard;

use App\Events\Leaderboard\LeaderboardUpdated;
use App\Models\Contest;
use App\Models\TypingResult;
use App\Repositories\Leaderboard\LeaderboardRepositoryInterface;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;

class LeaderboardService
{
    private const CACHE_TTL_SECONDS = 120;
    private const REDIS_FETCH_MULTIPLIER = 5;
    private const REDIS_FETCH_CAP = 500;

    public function __construct(private readonly LeaderboardRepositoryInterface $repository) {}

    public function processTypingResult(TypingResult $result): void
    {
        if ($result->is_disqualified) {
            return;
        }

        DB::transaction(function () use ($result): void {
            $anomalyFlag = $this->detectAnomaly($result);

            $scoreBreakdown = $this->calculateScore(
                wpm: (int) $result->wpm,
                accuracy: (float) $result->accuracy,
                errors: (int) $result->errors,
                completionTimeMs: max(1, (int) $result->duration_seconds * 1000),
                contestDurationMs: max(1, (int) optional($result->contest)->duration_minutes * 60 * 1000)
            );

            $userScore = $this->repository->upsertUserScore($result, $scoreBreakdown, $anomalyFlag);
            $this->repository->upsertContestRanking($result, $userScore);
            $this->repository->recalculateContestRanks((int) $result->contest_id);

            $rankedRows = $this->repository->fetchContestLeaderboard((int) $result->contest_id, 10);
            $currentUserRank = $rankedRows->firstWhere('user_id', $result->user_id)?->rank;

            if ($currentUserRank === 1 && (float) $userScore->winning_bonus === 0.0) {
                $scoreBreakdown = $this->calculateScore(
                    wpm: (int) $result->wpm,
                    accuracy: (float) $result->accuracy,
                    errors: (int) $result->errors,
                    completionTimeMs: max(1, (int) $result->duration_seconds * 1000),
                    contestDurationMs: max(1, (int) optional($result->contest)->duration_minutes * 60 * 1000),
                    includeWinningBonus: true,
                );

                $userScore = $this->repository->upsertUserScore($result, $scoreBreakdown, $anomalyFlag);
                $this->repository->upsertContestRanking($result, $userScore);
                $this->repository->recalculateContestRanks((int) $result->contest_id);
            }

            $this->refreshScope('global', 'all-time');
            $this->refreshScope('daily', now()->toDateString());
            $this->refreshScope('weekly', now()->format('o-\\WW'));
            $this->refreshScope('monthly', now()->format('Y-m'));

            $countryCode = strtoupper((string) optional($result->user)->country);
            if ($countryCode !== '') {
                $this->refreshScope('country', 'all-time', $countryCode);
            }

            $this->syncRedisContest((int) $result->contest_id);
            $this->syncRedisScope('global', 'all-time', null);
            $this->syncRedisScope('daily', now()->toDateString(), null);
            $this->syncRedisScope('weekly', now()->format('o-\\WW'), null);
            $this->syncRedisScope('monthly', now()->format('Y-m'), null);

            if ($countryCode !== '') {
                $this->syncRedisScope('country', 'all-time', $countryCode);
            }

            $this->broadcastContest((int) $result->contest_id);
            $this->broadcastScope('global', 'all-time');
            $this->broadcastScope('daily', now()->toDateString());
            $this->broadcastScope('weekly', now()->format('o-\\WW'));
            $this->broadcastScope('monthly', now()->format('Y-m'));

            if ($countryCode !== '') {
                $this->broadcastScope('country', 'all-time', $countryCode);
            }
        });

        $this->invalidateContestCache((int) $result->contest_id);
        $this->invalidatePeriodCaches();

        if ($countryCode !== '') {
            $this->invalidateScopeCache('country', 'all-time', $countryCode);
        }
    }

    public function getGlobal(int $limit): Collection
    {
        return Cache::remember(
            $this->scopeCacheKey('global', 'all-time', null, $limit),
            self::CACHE_TTL_SECONDS,
            fn () => $this->readScopeFromRedis('global', 'all-time', null, $limit)
        );
    }

    public function getContest(int $contestId, int $limit): Collection
    {
        return Cache::remember(
            $this->contestCacheKey($contestId, $limit),
            self::CACHE_TTL_SECONDS,
            fn () => $this->readContestFromRedis($contestId, $limit)
        );
    }

    public function getTopTen(): Collection
    {
        return $this->getGlobal(10);
    }

    public function getDaily(int $limit): Collection
    {
        $period = now()->toDateString();

        return Cache::remember(
            $this->scopeCacheKey('daily', $period, null, $limit),
            self::CACHE_TTL_SECONDS,
            fn () => $this->readScopeFromRedis('daily', $period, null, $limit)
        );
    }

    public function getWeekly(int $limit): Collection
    {
        $period = now()->format('o-\\WW');

        return Cache::remember(
            $this->scopeCacheKey('weekly', $period, null, $limit),
            self::CACHE_TTL_SECONDS,
            fn () => $this->readScopeFromRedis('weekly', $period, null, $limit)
        );
    }

    public function getMonthly(int $limit): Collection
    {
        $period = now()->format('Y-m');

        return Cache::remember(
            $this->scopeCacheKey('monthly', $period, null, $limit),
            self::CACHE_TTL_SECONDS,
            fn () => $this->readScopeFromRedis('monthly', $period, null, $limit)
        );
    }

    public function getCountry(string $countryCode, int $limit): Collection
    {
        return Cache::remember(
            $this->scopeCacheKey('country', 'all-time', strtoupper($countryCode), $limit),
            self::CACHE_TTL_SECONDS,
            fn () => $this->readScopeFromRedis('country', 'all-time', strtoupper($countryCode), $limit)
        );
    }

    public function invalidateContestCache(Contest|int $contest): void
    {
        $contestId = $contest instanceof Contest ? $contest->id : $contest;

        Cache::forget($this->contestCacheKey($contestId, 10));
        Cache::forget($this->contestCacheKey($contestId, 50));
        Cache::forget($this->contestCacheKey($contestId, 100));
        Cache::forget($this->contestCacheKey($contestId, 200));
    }

    public function invalidatePeriodCaches(): void
    {
        foreach ([10, 50, 100, 200] as $limit) {
            Cache::forget($this->scopeCacheKey('global', 'all-time', null, $limit));
            Cache::forget($this->scopeCacheKey('daily', now()->toDateString(), null, $limit));
            Cache::forget($this->scopeCacheKey('weekly', now()->format('o-\\WW'), null, $limit));
            Cache::forget($this->scopeCacheKey('monthly', now()->format('Y-m'), null, $limit));
        }
    }

    public function calculateScore(
        int $wpm,
        float $accuracy,
        int $errors,
        int $completionTimeMs,
        int $contestDurationMs,
        bool $includeWinningBonus = false,
    ): array {
        $fastFinishBonus = $completionTimeMs <= (int) floor($contestDurationMs * 0.70) ? 10.0 : 0.0;
        $perfectAccuracyBonus = $accuracy >= 100.0 ? 15.0 : 0.0;
        $winningBonus = $includeWinningBonus ? 20.0 : 0.0;

        $bonusPoints = $fastFinishBonus + $perfectAccuracyBonus + $winningBonus;
        $baseScore = ($wpm * $accuracy) / 100.0;
        $score = round($baseScore - $errors + $bonusPoints, 2);

        return [
            'score' => max(0, $score),
            'base_score' => round($baseScore, 2),
            'bonus_points' => $bonusPoints,
            'fast_finish_bonus' => $fastFinishBonus,
            'perfect_accuracy_bonus' => $perfectAccuracyBonus,
            'winning_bonus' => $winningBonus,
        ];
    }

    private function refreshScope(string $type, string $periodKey, ?string $countryCode = null): void
    {
        $aggregates = $this->repository->aggregateScope($type, $periodKey, $countryCode);
        $historyRows = [];

        foreach ($aggregates as $index => $aggregate) {
            $rank = $index + 1;

            $data = [
                'rank' => $rank,
                'total_score' => round((float) $aggregate->total_score, 2),
                'total_matches' => (int) $aggregate->total_matches,
                'wins' => (int) $aggregate->wins,
                'avg_wpm' => (int) $aggregate->avg_wpm,
                'avg_accuracy' => (float) $aggregate->avg_accuracy,
                'avg_errors' => (int) $aggregate->avg_errors,
                'best_completion_time_ms' => (int) $aggregate->best_completion_time_ms,
            ];

            $userId = (int) $aggregate->user_id;

            $this->repository->upsertRankingRow($userId, $type, $periodKey, $countryCode, $data);
            $this->repository->upsertLeaderboardRow($userId, $type, $periodKey, $countryCode, $data);

            $historyRows[] = [
                'user_id' => $userId,
                'type' => $type,
                'period_key' => $periodKey,
                'country_code' => $countryCode,
                'contest_id' => null,
                'rank' => $rank,
                'previous_rank' => null,
                'rank_movement' => 0,
                'score' => $data['total_score'],
                'wpm' => $data['avg_wpm'],
                'accuracy' => $data['avg_accuracy'],
                'errors' => $data['avg_errors'],
                'completion_time_ms' => $data['best_completion_time_ms'],
                'meta' => json_encode(['source' => 'scope_refresh']),
                'recorded_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        $this->repository->insertRankingHistory($historyRows);
        $this->invalidateScopeCache($type, $periodKey, $countryCode);
    }

    private function syncRedisScope(string $type, string $periodKey, ?string $countryCode): void
    {
        if (! $this->supportsRedis()) {
            return;
        }

        $rows = $this->repository->fetchLeaderboard($type, $periodKey, $countryCode, self::REDIS_FETCH_CAP);
        $this->writeSortedSet($this->scopeRedisKey($type, $periodKey, $countryCode), $rows);
    }

    private function syncRedisContest(int $contestId): void
    {
        if (! $this->supportsRedis()) {
            return;
        }

        $rows = $this->repository->fetchContestLeaderboard($contestId, self::REDIS_FETCH_CAP);
        $this->writeSortedSet($this->contestRedisKey($contestId), $rows);
    }

    private function readScopeFromRedis(string $type, string $periodKey, ?string $countryCode, int $limit): Collection
    {
        if (! $this->supportsRedis()) {
            return $this->repository->fetchLeaderboard($type, $periodKey, $countryCode, $limit);
        }

        $key = $this->scopeRedisKey($type, $periodKey, $countryCode);
        $userIds = $this->readSortedSetIds($key, $limit);

        if ($userIds === []) {
            return $this->repository->fetchLeaderboard($type, $periodKey, $countryCode, $limit);
        }

        $rows = $this->repository->fetchLeaderboardByUserIds($type, $periodKey, $countryCode, $userIds);

        return $this->rankRows($rows, $userIds, $limit);
    }

    private function readContestFromRedis(int $contestId, int $limit): Collection
    {
        if (! $this->supportsRedis()) {
            return $this->repository->fetchContestLeaderboard($contestId, $limit);
        }

        $key = $this->contestRedisKey($contestId);
        $userIds = $this->readSortedSetIds($key, $limit);

        if ($userIds === []) {
            return $this->repository->fetchContestLeaderboard($contestId, $limit);
        }

        $rows = $this->repository->fetchContestLeaderboardByUserIds($contestId, $userIds);

        return $this->rankRows($rows, $userIds, $limit);
    }

    private function readSortedSetIds(string $key, int $limit): array
    {
        if (! $this->supportsRedis()) {
            return [];
        }

        $fetchLimit = min(max($limit * self::REDIS_FETCH_MULTIPLIER, $limit), self::REDIS_FETCH_CAP) - 1;
        $rows = Redis::zrevrange($key, 0, max(0, $fetchLimit));

        return array_values(array_map('intval', is_array($rows) ? $rows : []));
    }

    private function writeSortedSet(string $key, Collection $rows): void
    {
        if (! $this->supportsRedis()) {
            return;
        }

        Redis::del($key);

        if ($rows->isEmpty()) {
            return;
        }

        Redis::pipeline(function ($pipe) use ($key, $rows): void {
            foreach ($rows as $row) {
                $pipe->zadd($key, (float) $row->score, (string) $row->user_id);
            }
        });
    }

    private function rankRows(Collection $rows, array $orderedIds, int $limit): Collection
    {
        return $rows
            ->sort(function ($left, $right): int {
                $leftScore = [
                    -(float) $left->score,
                    -(float) ($left->accuracy ?? 0),
                    (int) ($left->errors ?? 0),
                    (int) ($left->completion_time_ms ?? 0),
                    (int) $left->user_id,
                ];

                $rightScore = [
                    -(float) $right->score,
                    -(float) ($right->accuracy ?? 0),
                    (int) ($right->errors ?? 0),
                    (int) ($right->completion_time_ms ?? 0),
                    (int) $right->user_id,
                ];

                return $leftScore <=> $rightScore;
            })
            ->values()
            ->take($limit);
    }

    private function detectAnomaly(TypingResult $result): ?string
    {
        if ((int) $result->wpm >= 260) {
            return 'wpm_spike';
        }

        if ((float) $result->accuracy >= 99.99 && (int) $result->wpm >= 180 && (int) $result->errors === 0) {
            return 'bot_like_submission';
        }

        $recentScores = $result->user
            ?->scores()
            ?->where('created_at', '>=', now()->subMinutes(2))
            ->count();

        if (($recentScores ?? 0) >= 6) {
            return 'suspicious_frequency';
        }

        return null;
    }

    private function cacheContest(int $contestId): void
    {
        Cache::put(
            $this->contestCacheKey($contestId, 10),
            $this->repository->fetchContestLeaderboard($contestId, 10),
            self::CACHE_TTL_SECONDS
        );
    }

    private function broadcastContest(int $contestId): void
    {
        $payload = $this->repository->fetchContestLeaderboard($contestId, 10)->map(function ($row): array {
            return [
                'rank' => $row->rank,
                'previous_rank' => $row->previous_rank,
                'rank_movement' => $row->rank_movement,
                'medal' => $row->medal,
                'username' => $row->user?->username,
                'avatar' => $row->user?->avatar,
                'country' => $row->user?->country,
                'score' => (float) $row->score,
                'wpm' => (int) $row->wpm,
                'accuracy' => (float) $row->accuracy,
                'errors' => (int) $row->errors,
            ];
        })->values()->all();

        $this->safeBroadcast(new LeaderboardUpdated('contest', (string) $contestId, $payload, contestId: $contestId));
    }

    private function broadcastScope(string $type, string $periodKey, ?string $countryCode = null): void
    {
        $payload = $this->repository->fetchLeaderboard($type, $periodKey, $countryCode, 10)
            ->map(function ($row): array {
                return [
                    'rank' => $row->rank,
                    'previous_rank' => $row->previous_rank,
                    'rank_movement' => $row->rank_movement,
                    'medal' => $row->medal,
                    'username' => $row->user?->username,
                    'avatar' => $row->user?->avatar,
                    'country' => $row->user?->country,
                    'score' => (float) $row->score,
                    'wpm' => (int) $row->wpm,
                    'accuracy' => (float) $row->accuracy,
                    'errors' => (int) $row->errors,
                ];
            })
            ->values()
            ->all();

        $this->safeBroadcast(new LeaderboardUpdated($type, $periodKey, $payload, countryCode: $countryCode));
    }

    private function scopeCacheKey(string $type, string $periodKey, ?string $countryCode, int $limit): string
    {
        return "leaderboard:v2:{$type}:{$periodKey}:" . ($countryCode ?: 'all') . ":{$limit}";
    }

    private function contestCacheKey(int $contestId, int $limit): string
    {
        return "leaderboard:v2:response:contest:{$contestId}:{$limit}";
    }

    private function invalidateScopeCache(string $type, string $periodKey, ?string $countryCode): void
    {
        foreach ([10, 50, 100, 200] as $limit) {
            Cache::forget($this->scopeCacheKey($type, $periodKey, $countryCode, $limit));
        }
    }

    private function scopeRedisKey(string $type, string $periodKey, ?string $countryCode): string
    {
        return 'leaderboard:' . $type . ':' . $periodKey . ':' . ($countryCode ?: 'all');
    }

    private function contestRedisKey(int $contestId): string
    {
        return 'leaderboard:contest:' . $contestId;
    }

    private function supportsRedis(): bool
    {
        return class_exists(\Redis::class);
    }

    private function safeBroadcast(object $event): void
    {
        if (config('broadcasting.default') === 'redis' && !class_exists(\Redis::class)) {
            return;
        }

        try {
            event($event);
        } catch (\Throwable $exception) {
            report($exception);
        }
    }
}
