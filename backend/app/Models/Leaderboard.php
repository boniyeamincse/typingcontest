<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Leaderboard extends Model
{
    protected $table = 'leaderboards';

    protected $fillable = [
        'user_id',
        'type',
        'period_key',
        'rank',
        'score',
        'wpm',
        'accuracy',
    ];

    protected $casts = [
        'rank' => 'integer',
        'score' => 'decimal:2',
        'wpm' => 'integer',
        'accuracy' => 'decimal:2',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
