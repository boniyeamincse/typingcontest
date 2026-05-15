<?php

namespace App\Events\Social;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class AchievementShared
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public readonly int $userId,
        public readonly string $achievementType,
        public readonly array $payload = [],
    ) {
    }
}
