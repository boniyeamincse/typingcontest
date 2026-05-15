<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @OA\Schema(schema="StatisticResource")
 */
class StatisticResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'avg_wpm'       => $this->avg_wpm,
            'highest_wpm'   => $this->highest_wpm,
            'avg_accuracy'  => (float) $this->avg_accuracy,
            'total_matches' => $this->total_matches,
            'total_wins'    => $this->total_wins,
            'win_rate'      => $this->total_matches > 0
                ? round(($this->total_wins / $this->total_matches) * 100, 1)
                : 0,
            'typing_streak'  => $this->typing_streak,
            'longest_streak' => $this->longest_streak,
            'typing_hours'   => $this->typing_minutes > 0
                ? round($this->typing_minutes / 60, 1)
                : 0,
            'total_points'  => $this->total_points,
            'global_rank'   => $this->resource->user?->global_rank,
            'weekly_rank'   => $this->weekly_rank,
            'monthly_rank'  => $this->monthly_rank,
            'country_rank'  => $this->country_rank,
        ];
    }
}
