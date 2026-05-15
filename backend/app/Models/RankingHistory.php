<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RankingHistory extends Model
{
    protected $table = 'ranking_history';

    protected $fillable = [
        'user_id',
        'type',
        'period_key',
        'country_code',
        'contest_id',
        'rank',
        'previous_rank',
        'rank_movement',
        'score',
        'wpm',
        'accuracy',
        'errors',
        'completion_time_ms',
        'meta',
        'recorded_at',
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
        'meta' => 'array',
        'recorded_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function contest(): BelongsTo
    {
        return $this->belongsTo(Contest::class);
    }
}
