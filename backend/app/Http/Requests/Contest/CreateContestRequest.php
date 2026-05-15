<?php

namespace App\Http\Requests\Contest;

use App\Models\Contest;
use Illuminate\Foundation\Http\FormRequest;

class CreateContestRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasRole('admin') ?? false;
    }

    public function rules(): array
    {
        return [
            'title'                   => ['required', 'string', 'max:255'],
            'description'             => ['nullable', 'string', 'max:2000'],
            'type'                    => ['required', 'string', 'in:' . implode(',', Contest::TYPES)],
            'start_time'              => ['nullable', 'date', 'after:now'],
            'max_participants'        => ['nullable', 'integer', 'min:2', 'max:10000'],
            'typing_text_id'          => ['nullable', 'integer', 'exists:typing_texts,id'],
            'text_content'            => ['nullable', 'string', 'min:20'],
            'duration_minutes'        => ['nullable', 'integer', 'min:1', 'max:180'],
            'allow_late_join'         => ['boolean'],
            'is_public'               => ['boolean'],
            // Contest rules
            'rules'                                  => ['nullable', 'array'],
            'rules.duration_minutes'                 => ['nullable', 'integer', 'min:1', 'max:180'],
            'rules.max_wpm_threshold'                => ['nullable', 'integer', 'min:50', 'max:500'],
            'rules.allow_paste'                      => ['boolean'],
            'rules.allow_late_join'                  => ['boolean'],
            'rules.late_join_grace_seconds'          => ['nullable', 'integer', 'min:0', 'max:300'],
            'rules.auto_submit_on_timeout'           => ['boolean'],
            'rules.anti_cheat_enabled'               => ['boolean'],
            'rules.track_keystrokes'                 => ['boolean'],
            'rules.max_tab_switches'                 => ['nullable', 'integer', 'min:0', 'max:100'],
        ];
    }
}
