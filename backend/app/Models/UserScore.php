<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserScore extends Model
{
    protected $table = 'user_scores';

    protected $fillable = [
        'user_id',
        'contest_id',
        'typing_result_id',
        'score',
        'wpm',
        'accuracy',
        'errors',
        'completion_time_ms',
        'bonus_points',
        'fast_finish_bonus',
        'perfect_accuracy_bonus',
        'winning_bonus',
        'anomaly_flag',
        'meta',
    ];

    protected $casts = [
        'score' => 'decimal:2',
        'wpm' => 'integer',
        'accuracy' => 'decimal:2',
        'errors' => 'integer',
        'completion_time_ms' => 'integer',
        'bonus_points' => 'decimal:2',
        'fast_finish_bonus' => 'decimal:2',
        'perfect_accuracy_bonus' => 'decimal:2',
        'winning_bonus' => 'decimal:2',
        'meta' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function contest(): BelongsTo
    {
        return $this->belongsTo(Contest::class);
    }

    public function typingResult(): BelongsTo
    {
        return $this->belongsTo(TypingResult::class);
    }
}
