<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ContestRanking extends Model
{
    protected $table = 'contest_rankings';

    protected $fillable = [
        'contest_id',
        'user_id',
        'typing_result_id',
        'rank',
        'previous_rank',
        'rank_movement',
        'score',
        'wpm',
        'accuracy',
        'errors',
        'completion_time_ms',
        'bonus_points',
        'medal',
    ];

    protected $casts = [
        'rank' => 'integer',
        'previous_rank' => 'integer',
        'rank_movement' => 'integer',
        'score' => 'decimal:2',
        'wpm' => 'integer',
        'accuracy' => 'decimal:2',
        'errors' => 'integer',
        'completion_time_ms' => 'integer',
        'bonus_points' => 'decimal:2',
    ];

    public function contest(): BelongsTo
    {
        return $this->belongsTo(Contest::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function typingResult(): BelongsTo
    {
        return $this->belongsTo(TypingResult::class);
    }
}
