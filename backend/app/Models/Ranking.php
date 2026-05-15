<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Ranking extends Model
{
    protected $table = 'rankings';

    protected $fillable = [
        'user_id',
        'type',
        'period_key',
        'country_code',
        'rank',
        'previous_rank',
        'rank_movement',
        'total_score',
        'total_matches',
        'wins',
        'avg_wpm',
        'avg_accuracy',
        'avg_errors',
        'meta',
    ];

    protected $casts = [
        'rank' => 'integer',
        'previous_rank' => 'integer',
        'rank_movement' => 'integer',
        'total_score' => 'decimal:2',
        'total_matches' => 'integer',
        'wins' => 'integer',
        'avg_wpm' => 'integer',
        'avg_accuracy' => 'decimal:2',
        'avg_errors' => 'integer',
        'meta' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
