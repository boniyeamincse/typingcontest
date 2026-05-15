<?php

namespace App\Events\Rewards;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class RewardUnlocked
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public readonly int $userId,
        public readonly string $badgeCode,
        public readonly array $payload = [],
    ) {
    }
}
