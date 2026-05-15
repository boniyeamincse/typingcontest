<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @OA\Schema(
 *   schema="UserProfile",
 *   @OA\Property(property="bio", type="string", nullable=true),
 *   @OA\Property(property="cover_photo", type="string", nullable=true),
 *   @OA\Property(property="social_links", type="object", nullable=true),
 *   @OA\Property(property="level", type="integer"),
 *   @OA\Property(property="is_verified", type="boolean"),
 *   @OA\Property(property="is_online", type="boolean"),
 * )
 */
class UserProfile extends Model
{
    protected $fillable = [
        'user_id',
        'bio',
        'cover_photo',
        'social_links',
        'featured_badge_id',
        'level',
        'is_verified',
        'is_online',
        'last_seen_at',
        'show_email',
        'show_activity',
        'show_match_history',
        'show_stats',
    ];

    protected $casts = [
        'social_links'    => 'array',
        'level'           => 'integer',
        'is_verified'     => 'boolean',
        'is_online'       => 'boolean',
        'last_seen_at'    => 'datetime',
        'show_email'      => 'boolean',
        'show_activity'   => 'boolean',
        'show_match_history' => 'boolean',
        'show_stats'      => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function featuredBadge(): BelongsTo
    {
        return $this->belongsTo(Badge::class, 'featured_badge_id');
    }
}
