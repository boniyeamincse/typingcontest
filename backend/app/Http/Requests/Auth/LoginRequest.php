<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class LoginRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $login = $this->login;

        if (! is_string($login) && is_string($this->email)) {
            $login = $this->email;
        }

        if (! is_string($login) && is_string($this->username)) {
            $login = $this->username;
        }

        $this->merge([
            'login' => is_string($login) ? trim($login) : $login,
        ]);
    }

    public function rules(): array
    {
        return [
            'login' => ['required', 'string', 'max:255'],
            'password' => ['required', 'string', 'min:8'],
            'remember' => ['nullable', 'boolean'],
            'device_name' => ['nullable', 'string', 'max:120'],
        ];
    }
}
