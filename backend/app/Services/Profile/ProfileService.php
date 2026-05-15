<?php

namespace App\Services\Profile;

use App\Models\User;
use App\Models\UserActivity;
use App\Repositories\Profile\ActivityRepositoryInterface;
use App\Repositories\Profile\ProfileRepositoryInterface;
use App\Repositories\Profile\StatisticRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class ProfileService
{
    public function __construct(
        private readonly ProfileRepositoryInterface   $profileRepo,
        private readonly StatisticRepositoryInterface $statisticRepo,
        private readonly ActivityRepositoryInterface  $activityRepo,
        private readonly AvatarService                $avatarService,
    ) {}

    /**
     * Return the authenticated user's full profile.
     */
    public function getMyProfile(User $user): User
    {
        $this->profileRepo->getOrCreateProfile($user);
        $this->statisticRepo->getOrCreate($user);

        return $user->load([
            'profile.featuredBadge',
            'statistic',
            'badges' => fn($q) => $q->withPivot('earned_at', 'is_featured'),
        ]);
    }

    /**
     * Return a public profile by username (respects privacy settings).
     */
    public function getPublicProfile(string $username): ?User
    {
        $user = $this->profileRepo->findByUsername($username);
        if (!$user) {
            return null;
        }

        $this->profileRepo->getOrCreateProfile($user);
        $this->statisticRepo->getOrCreate($user);

        return $user->load([
            'profile.featuredBadge',
            'statistic',
            'badges' => fn($q) => $q->withPivot('earned_at', 'is_featured'),
        ]);
    }

    /**
     * Update user basic info + extended profile fields.
     */
    public function updateProfile(User $user, array $data, ?string $ip = null): User
    {
        $userFields = array_intersect_key($data, array_flip([
            'name', 'username', 'country',
        ]));

        $profileFields = array_intersect_key($data, array_flip([
            'bio', 'social_links',
            'show_email', 'show_activity', 'show_match_history', 'show_stats',
        ]));

        if (!empty($userFields)) {
            $this->profileRepo->updateProfile($user, $userFields);
        }

        if (!empty($profileFields)) {
            $this->profileRepo->updateUserProfile($user, $profileFields);
        }

        $this->activityRepo->log(
            $user,
            UserActivity::TYPE_PROFILE_UPDATE,
            'Profile updated',
            array_keys($data),
            $ip
        );

        return $this->getMyProfile($user);
    }

    /**
     * Upload a new avatar, delete old one, store activity.
     */
    public function uploadAvatar(User $user, \Illuminate\Http\UploadedFile $file, ?string $ip = null): string
    {
        $url = $this->avatarService->store($user, $file);

        $this->activityRepo->log(
            $user,
            UserActivity::TYPE_AVATAR_UPLOAD,
            'Avatar uploaded',
            ['avatar_url' => $url],
            $ip
        );

        return $url;
    }

    /**
     * Return computed statistics for a user.
     */
    public function getStats(User $user): \App\Models\UserStatistic
    {
        return $this->statisticRepo->recalculate($user);
    }

    /**
     * Return paginated badge list.
     */
    public function getBadges(User $user): Collection
    {
        return $this->profileRepo->getUserBadges($user);
    }

    /**
     * Feature a specific badge on the profile.
     */
    public function featureBadge(User $user, int $badgeId): void
    {
        $this->profileRepo->setFeaturedBadge($user, $badgeId);
    }

    /**
     * Return paginated match history.
     */
    public function getMatchHistory(User $user, int $perPage = 15): LengthAwarePaginator
    {
        return $this->profileRepo->getMatchHistory($user, $perPage);
    }

    /**
     * Return paginated activity feed.
     */
    public function getActivity(User $user, int $perPage = 20): LengthAwarePaginator
    {
        return $this->activityRepo->getActivity($user, $perPage);
    }

    /**
     * Mark user as online/offline and update last_seen_at.
     */
    public function updateOnlineStatus(User $user, bool $isOnline): void
    {
        $profile = $this->profileRepo->getOrCreateProfile($user);
        $profile->update([
            'is_online'    => $isOnline,
            'last_seen_at' => $isOnline ? null : now(),
        ]);
    }
}
