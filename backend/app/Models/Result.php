<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Result extends Model
{
    use HasFactory;

    protected $table = 'contest_participants';

    protected $fillable = [
        'user_id',
        'contest_id',
        'wpm',
        'accuracy',
        'errors',
        'score',
        'rank',
        'submitted_at',
        'joined_at',
        'is_disqualified',
        'disqualified_reason',
    ];

    protected $casts = [
        'wpm'             => 'integer',
        'accuracy'        => 'decimal:2',
        'errors'          => 'integer',
        'score'           => 'decimal:2',
        'rank'            => 'integer',
        'submitted_at'    => 'datetime',
        'joined_at'       => 'datetime',
        'is_disqualified' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function contest(): BelongsTo
    {
        return $this->belongsTo(Contest::class);
    }

    /**
     * Calculate score based on WPM, accuracy, and errors
     * Formula: (WPM × accuracy) − errors
     */
    public function calculateScore(): float
    {
        return ($this->wpm * ($this->accuracy / 100)) - $this->errors;
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            // Keep participant creation minimal (join event),
            // score/submitted_at are set only on result submission.
        });

        static::updating(function ($model) {
            // Score can be computed by DB generated column in some environments,
            // so we intentionally avoid assigning it here.
        });
    }
}
