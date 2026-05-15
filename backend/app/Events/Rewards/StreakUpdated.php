<?php

namespace App\Events\Rewards;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class StreakUpdated
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public readonly int $userId,
        public readonly int $streak,
        public readonly int $longestStreak,
    ) {
    }
}
