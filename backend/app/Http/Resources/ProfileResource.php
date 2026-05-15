<?php

namespace App\Http\Resources;

use App\Services\Profile\AvatarService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Full authenticated-user profile resource.
 *
 * @OA\Schema(schema="ProfileResource")
 */
class ProfileResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        /** @var \App\Models\User $user */
        $user    = $this->resource;
        $profile = $user->relationLoaded('profile') ? $user->profile : null;
        $stat    = $user->relationLoaded('statistic') ? $user->statistic : null;

        $avatarService = app(AvatarService::class);

        return [
            'id'                  => $user->id,
            'username'            => $user->username,
            'name'                => $user->name,
            'email'               => $user->email,
            'avatar_url'          => $avatarService->url($user->avatar),
            'cover_photo_url'     => $profile ? $avatarService->url($profile->cover_photo) : null,
            'bio'                 => $profile?->bio,
            'country'             => $user->country,
            'plan_type'           => $user->plan_type,
            'subscription_status' => $user->subscription_status,
            'xp_points'           => $user->xp_points,
            'global_rank'         => $user->global_rank,
            'role'                => $user->roles->first()?->name,
            'is_verified'         => $profile?->is_verified ?? false,
            'is_online'           => $profile?->is_online ?? false,
            'last_seen_at'        => $profile?->last_seen_at?->toISOString(),
            'level'               => $profile?->level ?? 1,
            'social_links'        => $profile?->social_links ?? [],
            'featured_badge'      => $profile?->relationLoaded('featuredBadge') && $profile->featuredBadge
                ? new BadgeResource($profile->featuredBadge)
                : null,
            'privacy' => [
                'show_email'         => $profile?->show_email ?? false,
                'show_activity'      => $profile?->show_activity ?? true,
                'show_match_history' => $profile?->show_match_history ?? true,
                'show_stats'         => $profile?->show_stats ?? true,
            ],
            'statistics'          => $stat ? new StatisticResource($stat) : null,
            'badges'              => $user->relationLoaded('badges')
                ? BadgeResource::collection($user->badges)
                : [],
            'email_verified_at'   => $user->email_verified_at?->toISOString(),
            'created_at'          => $user->created_at->toISOString(),
        ];
    }
}
