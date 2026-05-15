<?php

namespace App\Services\Admin;

use App\Models\Badge;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class AdminBadgeRewardService
{
    public function listBadges()
    {
        return Badge::query()->latest('id')->paginate(30);
    }

    public function createBadge(array $payload): Badge
    {
        return Badge::create($payload);
    }

    public function assignBadge(User $user, int $badgeId): void
    {
        $user->badges()->syncWithoutDetaching([$badgeId]);
    }

    public function setXpRules(User $user, int $xp): User
    {
        $user->increment('xp_points', $xp);

        return $user->refresh();
    }

    public function seasonalRewards(array $userIds, int $xp): void
    {
        DB::table('users')->whereIn('id', $userIds)->increment('xp_points', $xp);
    }
}
