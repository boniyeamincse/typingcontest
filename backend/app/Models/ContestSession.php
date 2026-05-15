<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Per-user live session state for a contest.
 *
 * @OA\Schema(schema="ContestSession")
 */
class ContestSession extends Model
{
    protected $table = 'contest_sessions';

    public const STATUS_WAITING       = 'waiting';
    public const STATUS_TYPING        = 'typing';
    public const STATUS_SUBMITTED     = 'submitted';
    public const STATUS_DISQUALIFIED  = 'disqualified';

    protected $fillable = [
        'contest_id',
        'user_id',
        'session_token',
        'status',
        'joined_at',
        'started_typing_at',
        'submitted_at',
        'keystroke_data',
        'tab_switches',
        'paste_attempts',
        'ip_address',
        'user_agent',
        'device_fingerprint',
        'is_flagged',
    ];

    protected $casts = [
        'joined_at'          => 'datetime',
        'started_typing_at'  => 'datetime',
        'submitted_at'       => 'datetime',
        'keystroke_data'     => 'array',
        'tab_switches'       => 'integer',
        'paste_attempts'     => 'integer',
        'is_flagged'         => 'boolean',
    ];

    public function contest(): BelongsTo
    {
        return $this->belongsTo(Contest::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function isSubmitted(): bool
    {
        return $this->status === self::STATUS_SUBMITTED;
    }
}
