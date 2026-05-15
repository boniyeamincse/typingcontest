<?php

namespace App\Http\Requests\Contest;

use Illuminate\Foundation\Http\FormRequest;

class SubmitResultRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            // New payload mode
            'characters_typed' => ['required_without:wpm', 'integer', 'min:0'],
            'elapsed_seconds'  => ['required_without:wpm', 'numeric', 'min:1', 'max:10800'],

            // Legacy payload mode
            'wpm'              => ['required_without:characters_typed', 'integer', 'min:0', 'max:1000'],
            'accuracy'         => ['required_without:characters_typed', 'numeric', 'min:0', 'max:100'],

            // Shared field
            'errors'           => ['required', 'integer', 'min:0'],
            'keystroke_data'   => ['nullable', 'array'],
        ];
    }
}
