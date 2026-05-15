<?php

namespace App\Http\Resources\Contest;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ContestResultResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'contest_id' => $this->contest_id,
            'user_id'    => $this->user_id,
            'rank'       => $this->rank,
            'wpm'        => $this->wpm,
            'accuracy'   => $this->accuracy,
            'errors'     => $this->errors,
            'score'      => $this->score,
            'joined_at'  => $this->joined_at?->toISOString(),
            'submitted_at' => $this->submitted_at?->toISOString(),
            'is_disqualified' => $this->is_disqualified,
            'disqualified_reason' => $this->disqualified_reason,
        ];
    }
}
