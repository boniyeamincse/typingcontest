<?php

namespace App\Http\Resources\Typing;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TypingSessionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'session_uuid' => $this->session_uuid,
            'contest_id' => $this->contest_id,
            'user_id' => $this->user_id,
            'status' => $this->status,
            'duration_seconds' => $this->duration_seconds,
            'countdown_seconds' => $this->countdown_seconds,
            'countdown_started_at' => $this->countdown_started_at?->toISOString(),
            'started_at' => $this->started_at?->toISOString(),
            'expires_at' => $this->expires_at?->toISOString(),
            'submitted_at' => $this->submitted_at?->toISOString(),
            'auto_submitted' => $this->auto_submitted,
            'is_flagged' => $this->is_flagged,
            'disqualified_reason' => $this->disqualified_reason,
            'last_sequence' => $this->last_sequence,
        ];
    }
}
