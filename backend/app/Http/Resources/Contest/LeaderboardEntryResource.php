<?php

namespace App\Http\Resources\Contest;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LeaderboardEntryResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'rank'     => $this->rank,
            'user'     => $this->when($this->relationLoaded('user'), fn () => [
                'id'       => $this->user?->id,
                'username' => $this->user?->username,
                'avatar'   => $this->user?->avatar,
                'country'  => $this->user?->country,
            ]),
            'wpm'      => $this->wpm,
            'accuracy' => $this->accuracy,
            'errors'   => $this->errors,
            'score'    => $this->score,
            'submitted_at' => $this->submitted_at?->toISOString(),
        ];
    }
}
