<?php

namespace App\Repositories\Profile;

use App\Models\User;
use App\Models\UserStatistic;

interface StatisticRepositoryInterface
{
    public function getOrCreate(User $user): UserStatistic;

    public function recalculate(User $user): UserStatistic;

    public function updateRanks(User $user, array $ranks): void;
}
