<?php

namespace App\Jobs;

use App\Models\Contest;
use App\Repositories\Contest\ParticipantRepositoryInterface;
use App\Services\Contest\LeaderboardService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class RecalculateRankings implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public string $queue = 'leaderboard';

    public function __construct(private readonly int $contestId) {}

    public function handle(
        ParticipantRepositoryInterface $participantRepo,
        LeaderboardService             $leaderboardService,
    ): void {
        $contest = Contest::find($this->contestId);

        if (!$contest) {
            return;
        }

        $participantRepo->recalculateRanks($contest);
        $leaderboardService->invalidateContestCache($contest);
    }
}
