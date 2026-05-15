<?php

namespace App\Http\Requests\Typing;

use Illuminate\Foundation\Http\FormRequest;

class TypingUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'session_id' => ['required', 'integer', 'exists:typing_sessions,id'],
            'sequence' => ['required', 'integer', 'min:1'],
            'typed_text' => ['required', 'string', 'max:50000'],
            'elapsed_ms' => ['required', 'integer', 'min:1'],
            'cursor_position' => ['nullable', 'integer', 'min:0'],
            'sync_interval_ms' => ['nullable', 'integer', 'min:200', 'max:500'],
            'payload' => ['nullable', 'array'],

            'error_event' => ['nullable', 'boolean'],
            'word_index' => ['nullable', 'integer', 'min:0'],
            'char_index' => ['nullable', 'integer', 'min:0'],
            'expected_char' => ['nullable', 'string', 'max:8'],
            'typed_char' => ['nullable', 'string', 'max:8'],
            'error_type' => ['nullable', 'string', 'max:50'],

            'paste_detected' => ['nullable', 'boolean'],
            'tab_switched' => ['nullable', 'boolean'],
            'focus_lost' => ['nullable', 'boolean'],
            'bot_pattern' => ['nullable', 'boolean'],
            'previous_wpm' => ['nullable', 'integer', 'min:0'],
            'device_fingerprint' => ['nullable', 'string', 'max:255'],
        ];
    }
}
