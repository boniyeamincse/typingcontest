<?php

namespace App\Http\Requests\Typing;

use Illuminate\Foundation\Http\FormRequest;

class SubmitTypingSessionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'session_id' => ['required', 'integer', 'exists:typing_sessions,id'],
            'typed_text' => ['required', 'string', 'max:50000'],
            'elapsed_ms' => ['required', 'integer', 'min:1'],
            'auto_submit' => ['nullable', 'boolean'],
            'paste_detected' => ['nullable', 'boolean'],
            'tab_switched' => ['nullable', 'boolean'],
            'focus_lost' => ['nullable', 'boolean'],
            'bot_pattern' => ['nullable', 'boolean'],
            'previous_wpm' => ['nullable', 'integer', 'min:0'],
            'device_fingerprint' => ['nullable', 'string', 'max:255'],
        ];
    }
}
