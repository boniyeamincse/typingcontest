<?php

namespace App\Http\Resources;

use App\Services\Profile\AvatarService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Public profile — respects privacy settings.
 *
 * @OA\Schema(schema="PublicProfileResource")
 */
class PublicProfileResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        /** @var \App\Models\User $user */
        $user    = $this->resource;
        $profile = $user->relationLoaded('profile') ? $user->profile : null;
        $stat    = $user->relationLoaded('statistic') ? $user->statistic : null;

        $avatarService = app(AvatarService::class);
        $showStats     = $profile?->show_stats ?? true;

        $data = [
            'username'       => $user->username,
            'name'           => $user->name,
            'avatar_url'     => $avatarService->url($user->avatar),
            'cover_photo_url' => $profile ? $avatarService->url($profile->cover_photo) : null,
            'bio'            => $profile?->bio,
            'country'        => $user->country,
            'plan_type'      => $user->plan_type,
            'xp_points'      => $user->xp_points,
            'global_rank'    => $user->global_rank,
            'is_verified'    => $profile?->is_verified ?? false,
            'is_online'      => $profile?->is_online ?? false,
            'last_seen_at'   => $profile?->last_seen_at?->toISOString(),
            'level'          => $profile?->level ?? 1,
            'social_links'   => $profile?->social_links ?? [],
            'featured_badge' => $profile?->relationLoaded('featuredBadge') && $profile->featuredBadge
                ? new BadgeResource($profile->featuredBadge)
                : null,
            'statistics'     => $showStats && $stat ? new StatisticResource($stat) : null,
            'badges'         => $user->relationLoaded('badges')
                ? BadgeResource::collection($user->badges)
                : [],
            'member_since'   => $user->created_at->toISOString(),
        ];

        // Conditionally expose email
        if ($profile?->show_email) {
            $data['email'] = $user->email;
        }

        return $data;
    }
}
