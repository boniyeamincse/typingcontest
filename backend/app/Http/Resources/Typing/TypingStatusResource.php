<?php

namespace App\Http\Resources\Typing;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TypingStatusResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'session' => new TypingSessionResource($this['session']),
            'live' => $this['live'] ?? [],
        ];
    }
}
