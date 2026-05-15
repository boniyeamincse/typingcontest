<?php

namespace App\Http\Requests\Profile;

use Illuminate\Foundation\Http\FormRequest;

/**
 * @OA\Schema(
 *   schema="UploadAvatarRequest",
 *   required={"avatar"},
 *   @OA\Property(property="avatar", type="string", format="binary"),
 * )
 */
class UploadAvatarRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'avatar' => [
                'required',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:2048', // 2 MB
                'dimensions:min_width=50,min_height=50,max_width=2000,max_height=2000',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'avatar.required'   => 'An avatar image is required.',
            'avatar.image'      => 'The uploaded file must be an image.',
            'avatar.mimes'      => 'Avatar must be a JPEG, PNG, or WebP file.',
            'avatar.max'        => 'Avatar must not exceed 2 MB.',
            'avatar.dimensions' => 'Avatar must be between 50×50 and 2000×2000 pixels.',
        ];
    }
}
