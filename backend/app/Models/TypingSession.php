<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class TypingSession extends Model
{
    use HasFactory;

    public const STATUS_PENDING = 'pending';
    public const STATUS_COUNTDOWN = 'countdown';
    public const STATUS_ACTIVE = 'active';
    public const STATUS_SUBMITTED = 'submitted';
    public const STATUS_EXPIRED = 'expired';
    public const STATUS_DISQUALIFIED = 'disqualified';

    protected $fillable = [
        'contest_id',
        'user_id',
        'session_uuid',
        'status',
        'duration_seconds',
        'countdown_started_at',
        'started_at',
        'expires_at',
        'submitted_at',
        'countdown_seconds',
        'auto_submitted',
        'is_flagged',
        'disqualified_reason',
        'last_sequence',
        'ip_address',
        'user_agent',
        'device_fingerprint',
    ];

    protected $casts = [
        'countdown_started_at' => 'datetime',
        'started_at' => 'datetime',
        'expires_at' => 'datetime',
        'submitted_at' => 'datetime',
        'duration_seconds' => 'integer',
        'countdown_seconds' => 'integer',
        'auto_submitted' => 'boolean',
        'is_flagged' => 'boolean',
        'last_sequence' => 'integer',
    ];

    public function contest(): BelongsTo
    {
        return $this->belongsTo(Contest::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function result(): HasOne
    {
        return $this->hasOne(TypingResult::class);
    }

    public function inputs(): HasMany
    {
        return $this->hasMany(TypingInput::class);
    }

    public function errors(): HasMany
    {
        return $this->hasMany(TypingError::class);
    }

    public function progressLogs(): HasMany
    {
        return $this->hasMany(TypingProgressLog::class);
    }

    public function isFinalized(): bool
    {
        return in_array($this->status, [self::STATUS_SUBMITTED, self::STATUS_EXPIRED, self::STATUS_DISQUALIFIED], true);
    }
}
