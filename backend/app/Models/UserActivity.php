<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @OA\Schema(
 *   schema="UserActivity",
 *   @OA\Property(property="type", type="string"),
 *   @OA\Property(property="description", type="string"),
 *   @OA\Property(property="metadata", type="object", nullable=true),
 *   @OA\Property(property="created_at", type="string", format="date-time"),
 * )
 */
class UserActivity extends Model
{
    const UPDATED_AT = null;

    protected $fillable = [
        'user_id',
        'type',
        'description',
        'metadata',
        'ip_address',
    ];

    protected $casts = [
        'metadata'   => 'array',
        'created_at' => 'datetime',
    ];

    // Activity type constants
    const TYPE_LOGIN              = 'login';
    const TYPE_LOGOUT             = 'logout';
    const TYPE_PROFILE_UPDATE     = 'profile_update';
    const TYPE_AVATAR_UPLOAD      = 'avatar_upload';
    const TYPE_CONTEST_JOIN       = 'contest_join';
    const TYPE_CONTEST_COMPLETE   = 'contest_complete';
    const TYPE_BADGE_UNLOCK       = 'badge_unlock';
    const TYPE_SUBSCRIPTION_UPGRADE = 'subscription_upgrade';
    const TYPE_RANK_CHANGE        = 'rank_change';

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
