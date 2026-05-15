<?php

namespace App\Http\Resources\Contest;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ContestResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'               => $this->id,
            'title'            => $this->title,
            'description'      => $this->description,
            'type'             => $this->type,
            'status'           => $this->status,
            'is_paused'        => $this->is_paused,
            'is_public'        => $this->is_public,
            'start_time'       => $this->start_time?->toISOString(),
            'started_at'       => $this->started_at?->toISOString(),
            'ended_at'         => $this->ended_at?->toISOString(),
            'duration_minutes' => $this->duration_minutes,
            'allow_late_join'  => $this->allow_late_join,
            'max_participants' => $this->max_participants,
            'participant_count' => $this->whenCounted('participants'),
            'creator'          => $this->when($this->relationLoaded('creator'), fn () => [
                'id'       => $this->creator?->id,
                'username' => $this->creator?->username,
                'avatar'   => $this->creator?->avatar,
            ]),
            'created_at'       => $this->created_at?->toISOString(),
            'updated_at'       => $this->updated_at?->toISOString(),
        ];
    }
}
