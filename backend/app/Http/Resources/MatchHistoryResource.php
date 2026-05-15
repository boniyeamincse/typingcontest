<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @OA\Schema(schema="MatchHistoryResource")
 */
class MatchHistoryResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'            => $this->id,
            'contest_id'    => $this->contest_id,
            'contest_name'  => $this->contest?->name ?? 'Unknown Contest',
            'contest_status' => $this->contest?->status,
            'wpm'           => $this->wpm,
            'accuracy'      => (float) $this->accuracy,
            'errors'        => $this->errors,
            'score'         => (float) $this->score,
            'rank'          => $this->rank,
            'result_status' => $this->rank === 1 ? 'win' : 'participated',
            'played_at'     => $this->submitted_at?->toISOString() ?? $this->created_at?->toISOString(),
        ];
    }
}
