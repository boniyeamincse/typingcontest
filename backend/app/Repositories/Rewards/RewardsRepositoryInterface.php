<?php

namespace App\Repositories\Rewards;

interface RewardsRepositoryInterface
{
    public function addXp(int $userId, int $xp, string $source, array $meta = []): int;

    public function getUserXp(int $userId): int;

    public function updateStreak(int $userId): array;

    public function resetStreak(int $userId): void;
}
