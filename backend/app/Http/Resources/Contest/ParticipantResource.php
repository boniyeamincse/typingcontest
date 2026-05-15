<?php

namespace App\Http\Resources\Contest;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ParticipantResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'user'               => $this->when($this->relationLoaded('user'), fn () => [
                'id'       => $this->user?->id,
                'username' => $this->user?->username,
                'avatar'   => $this->user?->avatar,
                'country'  => $this->user?->country,
            ]),
            'rank'               => $this->rank,
            'wpm'                => $this->wpm,
            'accuracy'           => $this->accuracy,
            'errors'             => $this->errors,
            'score'              => $this->score,
            'joined_at'          => $this->joined_at?->toISOString(),
            'submitted_at'       => $this->submitted_at?->toISOString(),
            'is_disqualified'    => $this->is_disqualified,
            'disqualified_reason' => $this->when($this->is_disqualified, $this->disqualified_reason),
        ];
    }
}
