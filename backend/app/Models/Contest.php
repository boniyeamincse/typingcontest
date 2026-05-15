<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Contest extends Model
{
    use HasFactory;

    public const STATUS_DRAFT = 'draft';
    public const STATUS_PUBLISHED = 'published';
    public const STATUS_ACTIVE = 'active';
    public const STATUS_FINISHED = 'finished';
    public const STATUS_CANCELLED = 'cancelled';

    public const STATUSES = [
        self::STATUS_DRAFT,
        self::STATUS_PUBLISHED,
        self::STATUS_ACTIVE,
        self::STATUS_FINISHED,
        self::STATUS_CANCELLED,
    ];

    public const TYPE_DAILY      = 'daily';
    public const TYPE_WEEKLY     = 'weekly';
    public const TYPE_MONTHLY    = 'monthly';
    public const TYPE_TOURNAMENT = 'tournament';
    public const TYPE_SPECIAL    = 'special';

    public const TYPES = [
        self::TYPE_DAILY, self::TYPE_WEEKLY, self::TYPE_MONTHLY,
        self::TYPE_TOURNAMENT, self::TYPE_SPECIAL,
    ];

    protected $fillable = [
        'title',
        'slug',
        'type',
        'status',
        'max_participants',
        'prize_description',
        'typing_text_id',
        'text_content',
        'created_by',
        'start_time',
        'end_time',
        'duration_minutes',
        'allow_late_join',
        'started_at',
        'ended_at',
        'is_paused',
    ];

    protected $casts = [
        'start_time'        => 'datetime',
        'end_time'          => 'datetime',
        'started_at'        => 'datetime',
        'ended_at'          => 'datetime',
        'max_participants'  => 'integer',
        'duration_minutes'  => 'integer',
        'allow_late_join'   => 'boolean',
        'is_paused'         => 'boolean',
    ];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function typingText(): BelongsTo
    {
        return $this->belongsTo(TypingText::class, 'typing_text_id');
    }

    public function results(): HasMany
    {
        return $this->hasMany(Result::class);
    }

    public function participants(): HasMany
    {
        return $this->hasMany(Result::class);
    }

    public function antiCheatLogs(): HasMany
    {
        return $this->hasMany(AntiCheatLog::class);
    }

    public function rule(): HasOne
    {
        return $this->hasOne(ContestRule::class);
    }

    public function sessions(): HasMany
    {
        return $this->hasMany(ContestSession::class);
    }

    // ── Helpers ──────────────────────────────────────────────────────────────

    public function isJoinable(): bool
    {
        if ($this->is_paused) {
            return false;
        }

        if (in_array($this->status, [self::STATUS_DRAFT, self::STATUS_CANCELLED, self::STATUS_FINISHED])) {
            return false;
        }

        if ($this->max_participants && $this->participants()->count() >= $this->max_participants) {
            return false;
        }

        return true;
    }

    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE && !$this->is_paused;
    }

    public function getTypingContent(): ?string
    {
        return $this->text_content ?? $this->typingText?->content;
    }

    public function scopePublished($query)
    {
        return $query->where('status', self::STATUS_PUBLISHED);
    }

    public function scopeActive($query)
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }

    public function scopeFinished($query)
    {
        return $query->where('status', self::STATUS_FINISHED);
    }

    public function scopeDraft($query)
    {
        return $query->where('status', self::STATUS_DRAFT);
    }

    public function scopeVisible($query)
    {
        return $query->whereIn('status', [
            self::STATUS_PUBLISHED,
            self::STATUS_ACTIVE,
            self::STATUS_FINISHED,
        ]);
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (!$model->slug) {
                $model->slug = Str::slug($model->title) . '-' . Str::random(6);
            }
        });
    }
}
