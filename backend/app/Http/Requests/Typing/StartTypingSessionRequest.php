<?php

namespace App\Http\Requests\Typing;

use Illuminate\Foundation\Http\FormRequest;

class StartTypingSessionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'contest_id' => ['required', 'integer', 'exists:contests,id'],
            'device_fingerprint' => ['nullable', 'string', 'max:255'],
        ];
    }
}
