<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @OA\Schema(schema="ActivityResource")
 */
class ActivityResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'          => $this->id,
            'type'        => $this->type,
            'description' => $this->description,
            'metadata'    => $this->metadata,
            'created_at'  => $this->created_at->toISOString(),
        ];
    }
}
