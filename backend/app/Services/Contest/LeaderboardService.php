<?php

namespace App\Services\Contest;

use App\Models\Contest;
use Illuminate\Database\Eloquent\Collection;
use App\Services\Leaderboard\LeaderboardService as CoreLeaderboardService;

class LeaderboardService
{
    public function __construct(private readonly CoreLeaderboardService $leaderboardService) {}

    public function getContestLeaderboard(Contest $contest, int $limit = 50): Collection
    {
        return $this->leaderboardService->getContest($contest->id, $limit);
    }

    public function getGlobalLeaderboard(int $limit = 50): Collection
    {
        return $this->leaderboardService->getGlobal($limit);
    }

    public function getDailyLeaderboard(int $limit = 50): Collection
    {
        return $this->leaderboardService->getDaily($limit);
    }

    public function getWeeklyLeaderboard(int $limit = 50): Collection
    {
        return $this->leaderboardService->getWeekly($limit);
    }

    public function getMonthlyLeaderboard(int $limit = 50): Collection
    {
        return $this->leaderboardService->getMonthly($limit);
    }

    public function getCountryLeaderboard(string $countryCode, int $limit = 50): Collection
    {
        return $this->leaderboardService->getCountry($countryCode, $limit);
    }

    public function invalidateContestCache(Contest $contest): void
    {
        $this->leaderboardService->invalidateContestCache($contest);
    }

    public function invalidatePeriodCaches(): void
    {
        $this->leaderboardService->invalidatePeriodCaches();
    }
}
