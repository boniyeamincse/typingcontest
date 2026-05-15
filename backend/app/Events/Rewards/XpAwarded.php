<?php

namespace App\Events\Rewards;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class XpAwarded
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public readonly int $userId,
        public readonly int $xp,
        public readonly string $source,
        public readonly int $totalXp,
        public readonly int $level,
    ) {
    }
}
