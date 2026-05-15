<?php

namespace App\Services\Contest;

use App\Models\Contest;
use App\Models\Leaderboard;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Cache;

class LeaderboardService
{
    private const CACHE_TTL_SECONDS = 60;

    public function getContestLeaderboard(Contest $contest, int $limit = 50): Collection
    {
        $cacheKey = "leaderboard:contest:{$contest->id}";

        return Cache::remember($cacheKey, self::CACHE_TTL_SECONDS, function () use ($contest, $limit) {
            return $contest->participants()
                ->with('user:id,username,avatar,country')
                ->where('is_disqualified', false)
                ->whereNotNull('submitted_at')
                ->orderBy('rank', 'asc')
                ->limit($limit)
                ->get();
        });
    }

    public function getGlobalLeaderboard(int $limit = 50): Collection
    {
        return Cache::remember('leaderboard:global', self::CACHE_TTL_SECONDS * 5, function () use ($limit) {
            return Leaderboard::with('user:id,username,avatar,country')
                ->where('type', 'global')
                ->orderBy('rank', 'asc')
                ->limit($limit)
                ->get();
        });
    }

    public function getDailyLeaderboard(int $limit = 50): Collection
    {
        $key = 'leaderboard:daily:' . now()->toDateString();

        return Cache::remember($key, self::CACHE_TTL_SECONDS * 5, function () use ($limit) {
            return Leaderboard::with('user:id,username,avatar,country')
                ->where('type', 'daily')
                ->where('period_key', now()->toDateString())
                ->orderBy('rank', 'asc')
                ->limit($limit)
                ->get();
        });
    }

    public function getWeeklyLeaderboard(int $limit = 50): Collection
    {
        $key = 'leaderboard:weekly:' . now()->format('Y-W');

        return Cache::remember($key, self::CACHE_TTL_SECONDS * 10, function () use ($limit) {
            return Leaderboard::with('user:id,username,avatar,country')
                ->where('type', 'weekly')
                ->where('period_key', now()->format('Y-W'))
                ->orderBy('rank', 'asc')
                ->limit($limit)
                ->get();
        });
    }

    public function getMonthlyLeaderboard(int $limit = 50): Collection
    {
        $key = 'leaderboard:monthly:' . now()->format('Y-m');

        return Cache::remember($key, self::CACHE_TTL_SECONDS * 30, function () use ($limit) {
            return Leaderboard::with('user:id,username,avatar,country')
                ->where('type', 'monthly')
                ->where('period_key', now()->format('Y-m'))
                ->orderBy('rank', 'asc')
                ->limit($limit)
                ->get();
        });
    }

    public function getCountryLeaderboard(string $countryCode, int $limit = 50): Collection
    {
        $key = "leaderboard:country:{$countryCode}";

        return Cache::remember($key, self::CACHE_TTL_SECONDS * 5, function () use ($countryCode, $limit) {
            return Leaderboard::with('user:id,username,avatar,country')
                ->whereHas('user', fn ($q) => $q->where('country', $countryCode))
                ->where('type', 'global')
                ->orderBy('rank', 'asc')
                ->limit($limit)
                ->get();
        });
    }

    public function invalidateContestCache(Contest $contest): void
    {
        Cache::forget("leaderboard:contest:{$contest->id}");
    }

    public function invalidatePeriodCaches(): void
    {
        Cache::forget('leaderboard:global');
        Cache::forget('leaderboard:daily:' . now()->toDateString());
        Cache::forget('leaderboard:weekly:' . now()->format('Y-W'));
        Cache::forget('leaderboard:monthly:' . now()->format('Y-m'));
    }
}
