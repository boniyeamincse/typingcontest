<?php

namespace App\Http\Resources\Typing;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TypingResultResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'typing_session_id' => $this->typing_session_id,
            'contest_id' => $this->contest_id,
            'user_id' => $this->user_id,
            'correct_words' => $this->correct_words,
            'correct_characters' => $this->correct_characters,
            'total_characters' => $this->total_characters,
            'errors' => $this->errors,
            'wpm' => $this->wpm,
            'cpm' => $this->cpm,
            'accuracy' => $this->accuracy,
            'score' => $this->score,
            'progress_percent' => $this->progress_percent,
            'duration_seconds' => $this->duration_seconds,
            'is_disqualified' => $this->is_disqualified,
            'disqualified_reason' => $this->disqualified_reason,
            'meta' => $this->meta,
            'created_at' => $this->created_at?->toISOString(),
        ];
    }
}
