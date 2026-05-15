<?php

namespace App\Jobs\Leaderboard;

use App\Models\TypingResult;
use App\Services\Leaderboard\LeaderboardService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class UpdateLeaderboardRankingsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public string $queue = 'leaderboard';

    public function __construct(private readonly int $typingResultId) {}

    public function handle(LeaderboardService $leaderboardService): void
    {
        $result = TypingResult::with(['user', 'contest'])->find($this->typingResultId);

        if (!$result) {
            return;
        }

        $leaderboardService->processTypingResult($result);
    }
}
