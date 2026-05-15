<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CountryRanking extends Model
{
    protected $table = 'country_rankings';

    protected $fillable = [
        'country_code',
        'country_name',
        'total_score',
        'avg_wpm',
        'participant_count',
        'rank',
        'period_key',
    ];

    protected $casts = [
        'total_score' => 'decimal:2',
        'avg_wpm' => 'decimal:2',
        'participant_count' => 'integer',
        'rank' => 'integer',
    ];
}
