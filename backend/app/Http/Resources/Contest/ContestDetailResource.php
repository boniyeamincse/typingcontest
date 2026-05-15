<?php

namespace App\Http\Resources\Contest;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ContestDetailResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'               => $this->id,
            'title'            => $this->title,
            'description'      => $this->description,
            'slug'             => $this->slug,
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
            'participant_count' => $this->participants()->count(),
            'text_content'     => $this->when(
                $this->isActive() || $this->status === 'finished',
                fn () => $this->getTypingContent()
            ),
            'rule'             => $this->when($this->relationLoaded('rule'), fn () => $this->rule ? [
                'duration_minutes'        => $this->rule->duration_minutes,
                'max_wpm_threshold'       => $this->rule->max_wpm_threshold,
                'allow_paste'             => $this->rule->allow_paste,
                'allow_late_join'         => $this->rule->allow_late_join,
                'late_join_grace_seconds' => $this->rule->late_join_grace_seconds,
                'auto_submit_on_timeout'  => $this->rule->auto_submit_on_timeout,
                'anti_cheat_enabled'      => $this->rule->anti_cheat_enabled,
                'track_keystrokes'        => $this->rule->track_keystrokes,
                'max_tab_switches'        => $this->rule->max_tab_switches,
            ] : null),
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
