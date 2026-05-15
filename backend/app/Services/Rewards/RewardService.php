<?php

namespace App\Services\Rewards;

use App\Events\Rewards\RewardUnlocked;

class RewardService
{
    public function unlockBadge(int $userId, string $badgeCode, array $payload = []): void
    {
        event(new RewardUnlocked($userId, $badgeCode, $payload));
    }
}
