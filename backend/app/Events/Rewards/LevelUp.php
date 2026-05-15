<?php

namespace App\Events\Rewards;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class LevelUp
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public readonly int $userId,
        public readonly int $fromLevel,
        public readonly int $toLevel,
        public readonly int $totalXp,
    ) {
    }
}
