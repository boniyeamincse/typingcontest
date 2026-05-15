<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
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

    protected $fillable = [
        'title',
        'slug',
        'type',
        'status',
        'max_participants',
        'prize_description',
        'typing_text_id',
        'created_by',
        'start_time',
        'end_time',
    ];

    protected $casts = [
        'start_time' => 'datetime',
        'end_time' => 'datetime',
        'max_participants' => 'integer',
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
