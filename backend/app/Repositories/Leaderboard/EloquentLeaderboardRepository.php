<?php

namespace App\Repositories\Leaderboard;

use App\Models\ContestRanking;
use App\Models\Leaderboard;
use App\Models\Ranking;
use App\Models\RankingHistory;
use App\Models\TypingResult;
use App\Models\UserScore;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class EloquentLeaderboardRepository implements LeaderboardRepositoryInterface
{
    public function upsertUserScore(TypingResult $result, array $scoreBreakdown, ?string $anomalyFlag = null): UserScore
    {
        return UserScore::query()->updateOrCreate(
            ['typing_result_id' => $result->id],
            [
                'user_id' => $result->user_id,
                'contest_id' => $result->contest_id,
                'score' => $scoreBreakdown['score'],
                'wpm' => $result->wpm,
                'accuracy' => $result->accuracy,
                'errors' => $result->errors,
                'completion_time_ms' => max(1, (int) $result->duration_seconds * 1000),
                'bonus_points' => $scoreBreakdown['bonus_points'],
                'fast_finish_bonus' => $scoreBreakdown['fast_finish_bonus'],
                'perfect_accuracy_bonus' => $scoreBreakdown['perfect_accuracy_bonus'],
                'winning_bonus' => $scoreBreakdown['winning_bonus'],
                'anomaly_flag' => $anomalyFlag,
                'meta' => [
                    'typing_result_id' => $result->id,
                    'base_score' => $scoreBreakdown['base_score'],
                ],
            ]
        );
    }

    public function upsertContestRanking(TypingResult $result, UserScore $userScore): ContestRanking
    {
        return ContestRanking::query()->updateOrCreate(
            [
                'contest_id' => $result->contest_id,
                'user_id' => $result->user_id,
            ],
            [
                'typing_result_id' => $result->id,
                'score' => $userScore->score,
                'wpm' => $userScore->wpm,
                'accuracy' => $userScore->accuracy,
                'errors' => $userScore->errors,
                'completion_time_ms' => $userScore->completion_time_ms,
                'bonus_points' => $userScore->bonus_points,
            ]
        );
    }

    public function recalculateContestRanks(int $contestId): Collection
    {
        $rows = ContestRanking::query()
            ->where('contest_id', $contestId)
            ->orderByDesc('score')
            ->orderByDesc('accuracy')
            ->orderBy('errors')
            ->orderBy('completion_time_ms')
            ->orderBy('updated_at')
            ->get();

        foreach ($rows as $index => $row) {
            $newRank = $index + 1;
            $previous = $row->rank ?: null;

            $row->update([
                'previous_rank' => $previous,
                'rank' => $newRank,
                'rank_movement' => $previous ? ($previous - $newRank) : 0,
                'medal' => $newRank === 1 ? 'gold' : ($newRank === 2 ? 'silver' : ($newRank === 3 ? 'bronze' : null)),
            ]);
        }

        return $rows->fresh(['user:id,username,avatar,country']);
    }

    public function fetchContestLeaderboard(int $contestId, int $limit = 10): Collection
    {
        return ContestRanking::query()
            ->with('user:id,username,avatar,country')
            ->where('contest_id', $contestId)
            ->orderBy('rank')
            ->limit($limit)
            ->get();
    }

    public function aggregateScope(string $type, ?string $periodKey = null, ?string $countryCode = null): Collection
    {
        $query = UserScore::query()
            ->join('users', 'users.id', '=', 'user_scores.user_id')
            ->selectRaw('user_scores.user_id')
            ->selectRaw('SUM(user_scores.score) as total_score')
            ->selectRaw('COUNT(*) as total_matches')
            ->selectRaw('SUM(CASE WHEN user_scores.winning_bonus > 0 THEN 1 ELSE 0 END) as wins')
            ->selectRaw('ROUND(AVG(user_scores.wpm), 0) as avg_wpm')
            ->selectRaw('ROUND(AVG(user_scores.accuracy), 2) as avg_accuracy')
            ->selectRaw('ROUND(AVG(user_scores.errors), 0) as avg_errors')
            ->selectRaw('MIN(user_scores.completion_time_ms) as best_completion_time_ms')
            ->groupBy('user_scores.user_id');

        if ($type === 'daily') {
            $query->whereDate('user_scores.created_at', now()->toDateString());
        }

        if ($type === 'weekly') {
            $query->whereBetween('user_scores.created_at', [now()->startOfWeek(), now()->endOfWeek()]);
        }

        if ($type === 'monthly') {
            $query->whereYear('user_scores.created_at', now()->year)->whereMonth('user_scores.created_at', now()->month);
        }

        if ($countryCode) {
            $query->where('users.country', strtoupper($countryCode));
        }

        return $query
            ->orderByDesc('total_score')
            ->orderByDesc('avg_accuracy')
            ->orderBy('avg_errors')
            ->orderBy('best_completion_time_ms')
            ->get();
    }

    public function upsertRankingRow(int $userId, string $type, ?string $periodKey, ?string $countryCode, array $data): void
    {
        $existing = Ranking::query()
            ->where('user_id', $userId)
            ->where('type', $type)
            ->where('period_key', $periodKey)
            ->where('country_code', $countryCode)
            ->first();

        $previous = $existing?->rank;
        $rank = (int) $data['rank'];

        Ranking::query()->updateOrCreate(
            [
                'user_id' => $userId,
                'type' => $type,
                'period_key' => $periodKey,
                'country_code' => $countryCode,
            ],
            [
                'rank' => $rank,
                'previous_rank' => $previous,
                'rank_movement' => $previous ? ($previous - $rank) : 0,
                'total_score' => $data['total_score'],
                'total_matches' => $data['total_matches'],
                'wins' => $data['wins'],
                'avg_wpm' => $data['avg_wpm'],
                'avg_accuracy' => $data['avg_accuracy'],
                'avg_errors' => $data['avg_errors'],
                'meta' => ['best_completion_time_ms' => $data['best_completion_time_ms']],
            ]
        );
    }

    public function upsertLeaderboardRow(int $userId, string $type, ?string $periodKey, ?string $countryCode, array $data): void
    {
        $existing = Leaderboard::query()
            ->where('user_id', $userId)
            ->where('type', $type)
            ->where('period_key', $periodKey)
            ->first();

        $previous = $existing?->rank;
        $rank = (int) $data['rank'];

        Leaderboard::query()->updateOrCreate(
            [
                'user_id' => $userId,
                'type' => $type,
                'period_key' => $periodKey,
            ],
            [
                'contest_id' => $data['contest_id'] ?? null,
                'country_code' => $countryCode,
                'rank' => $rank,
                'previous_rank' => $previous,
                'rank_movement' => $previous ? ($previous - $rank) : 0,
                'score' => $data['total_score'],
                'wpm' => $data['avg_wpm'],
                'accuracy' => $data['avg_accuracy'],
                'errors' => $data['avg_errors'],
                'completion_time_ms' => $data['best_completion_time_ms'],
                'bonus_points' => 0,
                'medal' => $rank === 1 ? 'gold' : ($rank === 2 ? 'silver' : ($rank === 3 ? 'bronze' : null)),
            ]
        );
    }

    public function fetchLeaderboard(string $type, ?string $periodKey = null, ?string $countryCode = null, int $limit = 10): Collection
    {
        return Leaderboard::query()
            ->with('user:id,username,avatar,country')
            ->where('type', $type)
            ->when($periodKey, fn ($q) => $q->where('period_key', $periodKey))
            ->when($countryCode, fn ($q) => $q->where('country_code', strtoupper($countryCode)))
            ->orderBy('rank')
            ->limit($limit)
            ->get();
    }

    public function insertRankingHistory(array $rows): void
    {
        if (empty($rows)) {
            return;
        }

        RankingHistory::query()->insert($rows);
    }
}
