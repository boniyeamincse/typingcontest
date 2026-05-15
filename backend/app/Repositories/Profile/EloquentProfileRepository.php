<?php

namespace App\Repositories\Profile;

use App\Models\User;
use App\Models\UserProfile;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class EloquentProfileRepository implements ProfileRepositoryInterface
{
    public function findByUsername(string $username): ?User
    {
        return User::where('username', $username)
            ->with(['profile.featuredBadge', 'statistic'])
            ->first();
    }

    public function getOrCreateProfile(User $user): UserProfile
    {
        return UserProfile::firstOrCreate(
            ['user_id' => $user->id],
            [
                'level'              => 1,
                'is_verified'        => false,
                'is_online'          => false,
                'show_email'         => false,
                'show_activity'      => true,
                'show_match_history' => true,
                'show_stats'         => true,
            ]
        );
    }

    public function updateProfile(User $user, array $data): User
    {
        $user->fill($data);
        $user->save();

        return $user->fresh(['profile', 'statistic']);
    }

    public function updateUserProfile(User $user, array $profileData): UserProfile
    {
        $profile = $this->getOrCreateProfile($user);
        $profile->fill($profileData);
        $profile->save();

        return $profile->fresh('featuredBadge');
    }

    public function updateAvatar(User $user, string $avatarPath): void
    {
        $user->update(['avatar' => $avatarPath]);
    }

    public function updateCoverPhoto(User $user, string $coverPath): void
    {
        $profile = $this->getOrCreateProfile($user);
        $profile->update(['cover_photo' => $coverPath]);
    }

    public function getMatchHistory(User $user, int $perPage = 15): LengthAwarePaginator
    {
        return $user->results()
            ->with('contest:id,name,status,started_at')
            ->orderByDesc('created_at')
            ->paginate($perPage);
    }

    public function getUserBadges(User $user): Collection
    {
        return $user->badges()
            ->withPivot('earned_at', 'is_featured')
            ->orderByPivot('earned_at', 'desc')
            ->get();
    }

    public function setFeaturedBadge(User $user, int $badgeId): void
    {
        // Clear old featured
        $user->badges()->updateExistingPivot(
            $user->badges()->pluck('badges.id')->toArray(),
            ['is_featured' => false]
        );
        // Set new featured
        $user->badges()->updateExistingPivot($badgeId, ['is_featured' => true]);
        // Store in profile for quick access
        $user->profile()->update(['featured_badge_id' => $badgeId]);
    }
}
