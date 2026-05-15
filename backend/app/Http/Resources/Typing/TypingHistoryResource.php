<?php

namespace App\Http\Resources\Typing;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TypingHistoryResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'session' => new TypingSessionResource($this),
            'contest' => [
                'id' => $this->contest?->id,
                'title' => $this->contest?->title,
                'type' => $this->contest?->type,
                'status' => $this->contest?->status,
            ],
            'result' => $this->result ? new TypingResultResource($this->result) : null,
        ];
    }
}
