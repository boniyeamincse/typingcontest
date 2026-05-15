<?php

namespace App\Http\Resources\Leaderboard;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LeaderboardEntryResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'rank' => (int) $this->rank,
            'username' => (string) ($this->user?->username ?? ''),
            'avatar' => $this->user?->avatar,
            'score' => (float) $this->score,
            'wpm' => (int) $this->wpm,
            'accuracy' => (float) $this->accuracy,
            'errors' => (int) ($this->errors ?? 0),
            'country' => $this->user?->country,
            'rank_movement' => (int) ($this->rank_movement ?? 0),
            'previous_rank' => $this->previous_rank ? (int) $this->previous_rank : null,
            'medal' => $this->medal,
        ];
    }
}
