<?php

namespace App\Http\Resources;

use App\Services\Profile\AvatarService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @OA\Schema(schema="BadgeResource")
 */
class BadgeResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $avatarService = app(AvatarService::class);

        return [
            'id'                => $this->id,
            'name'              => $this->name,
            'slug'              => $this->slug,
            'icon_url'          => $avatarService->url($this->icon_url) ?? $this->icon_url,
            'description'       => $this->description,
            'requirement_type'  => $this->requirement_type,
            'requirement_value' => $this->requirement_value,
            'is_premium'        => $this->is_premium,
            'earned_at'         => $this->whenPivotLoaded('user_badges', fn() =>
                $this->pivot?->earned_at?->toISOString()
            ),
            'is_featured'       => $this->whenPivotLoaded('user_badges', fn() =>
                (bool) $this->pivot?->is_featured
            ),
        ];
    }
}
