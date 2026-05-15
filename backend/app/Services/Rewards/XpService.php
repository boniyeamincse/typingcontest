<?php

namespace App\Services\Rewards;

use App\Events\Rewards\LevelUp;
use App\Events\Rewards\XpAwarded;
use App\Repositories\Rewards\RewardsRepositoryInterface;

class XpService
{
    public function __construct(private readonly RewardsRepositoryInterface $repository)
    {
    }

    public function awardXp(int $userId, int $xp, string $source, array $meta = []): array
    {
        $previousXp = $this->repository->getUserXp($userId);
        $currentXp = $this->repository->addXp($userId, $xp, $source, $meta);

        $previousLevel = $this->toLevel($previousXp);
        $currentLevel = $this->toLevel($currentXp);

        event(new XpAwarded($userId, $xp, $source, $currentXp, $currentLevel));

        if ($currentLevel > $previousLevel) {
            event(new LevelUp($userId, $previousLevel, $currentLevel, $currentXp));
        }

        return [
            'xp' => $currentXp,
            'level' => $currentLevel,
            'leveled_up' => $currentLevel > $previousLevel,
        ];
    }

    public function toLevel(int $xp): int
    {
        return max(1, (int) floor(sqrt($xp / 100)) + 1);
    }
}
