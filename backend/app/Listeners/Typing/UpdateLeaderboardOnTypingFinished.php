<?php

namespace App\Listeners\Typing;

use App\Events\Typing\TypingFinished;
use App\Jobs\Leaderboard\UpdateLeaderboardRankingsJob;
use Illuminate\Contracts\Queue\ShouldQueue;

class UpdateLeaderboardOnTypingFinished implements ShouldQueue
{
    public string $queue = 'leaderboard';

    public function handle(TypingFinished $event): void
    {
        UpdateLeaderboardRankingsJob::dispatch((int) $event->result->id);
    }
}
