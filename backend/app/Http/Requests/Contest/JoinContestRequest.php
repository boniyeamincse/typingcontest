<?php

namespace App\Http\Requests\Contest;

use Illuminate\Foundation\Http\FormRequest;

class JoinContestRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'device_fingerprint' => ['nullable', 'string', 'max:255'],
        ];
    }
}
