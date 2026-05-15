<?php

namespace App\Listeners\Contest;

use App\Events\Contest\ScoreUpdated;
use App\Services\Contest\LeaderboardService;
use Illuminate\Contracts\Queue\ShouldQueue;

class UpdateLeaderboardOnScoreSubmit implements ShouldQueue
{
    public string $queue = 'leaderboard';

    public function __construct(private readonly LeaderboardService $leaderboardService) {}

    public function handle(ScoreUpdated $event): void
    {
        $this->leaderboardService->invalidateContestCache($event->contest);
        $this->leaderboardService->invalidatePeriodCaches();
    }
}
