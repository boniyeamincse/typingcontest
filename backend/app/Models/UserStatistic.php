<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @OA\Schema(
 *   schema="UserStatistic",
 *   @OA\Property(property="avg_wpm", type="integer"),
 *   @OA\Property(property="highest_wpm", type="integer"),
 *   @OA\Property(property="avg_accuracy", type="number"),
 *   @OA\Property(property="total_matches", type="integer"),
 *   @OA\Property(property="total_wins", type="integer"),
 *   @OA\Property(property="typing_streak", type="integer"),
 *   @OA\Property(property="longest_streak", type="integer"),
 *   @OA\Property(property="typing_minutes", type="integer"),
 *   @OA\Property(property="total_points", type="integer"),
 * )
 */
class UserStatistic extends Model
{
    protected $fillable = [
        'user_id',
        'avg_wpm',
        'highest_wpm',
        'avg_accuracy',
        'total_matches',
        'total_wins',
        'typing_streak',
        'longest_streak',
        'typing_minutes',
        'total_points',
        'weekly_rank',
        'monthly_rank',
        'country_rank',
        'streak_last_date',
    ];

    protected $casts = [
        'avg_wpm'         => 'integer',
        'highest_wpm'     => 'integer',
        'avg_accuracy'    => 'decimal:2',
        'total_matches'   => 'integer',
        'total_wins'      => 'integer',
        'typing_streak'   => 'integer',
        'longest_streak'  => 'integer',
        'typing_minutes'  => 'integer',
        'total_points'    => 'integer',
        'weekly_rank'     => 'integer',
        'monthly_rank'    => 'integer',
        'country_rank'    => 'integer',
        'streak_last_date' => 'date',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
