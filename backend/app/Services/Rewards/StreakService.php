<?php

namespace App\Services\Rewards;

use App\Events\Rewards\StreakUpdated;
use App\Repositories\Rewards\RewardsRepositoryInterface;

class StreakService
{
    public function __construct(private readonly RewardsRepositoryInterface $repository)
    {
    }

    public function updateAfterActivity(int $userId): array
    {
        $result = $this->repository->updateStreak($userId);

        event(new StreakUpdated($userId, $result['streak'], $result['longest_streak']));

        return $result;
    }

    public function reset(int $userId): void
    {
        $this->repository->resetStreak($userId);
    }
}
