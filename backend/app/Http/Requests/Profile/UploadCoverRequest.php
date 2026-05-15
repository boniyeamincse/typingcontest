<?php

namespace App\Http\Requests\Profile;

use Illuminate\Foundation\Http\FormRequest;

class UploadCoverRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'cover' => [
                'required',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:4096', // 4 MB
                'dimensions:min_width=800,min_height=200,max_width=3840,max_height=1080',
            ],
        ];
    }
}
