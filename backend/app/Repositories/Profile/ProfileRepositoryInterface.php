<?php

namespace App\Repositories\Profile;

use App\Models\User;
use App\Models\UserProfile;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface ProfileRepositoryInterface
{
    public function findByUsername(string $username): ?User;

    public function getOrCreateProfile(User $user): UserProfile;

    public function updateProfile(User $user, array $data): User;

    public function updateUserProfile(User $user, array $profileData): UserProfile;

    public function updateAvatar(User $user, string $avatarPath): void;

    public function updateCoverPhoto(User $user, string $coverPath): void;

    public function getMatchHistory(User $user, int $perPage = 15): LengthAwarePaginator;

    public function getUserBadges(User $user): \Illuminate\Database\Eloquent\Collection;

    public function setFeaturedBadge(User $user, int $badgeId): void;
}
