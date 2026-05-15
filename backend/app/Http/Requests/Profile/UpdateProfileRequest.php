<?php

namespace App\Http\Requests\Profile;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * @OA\Schema(
 *   schema="UpdateProfileRequest",
 *   @OA\Property(property="name", type="string", maxLength=255),
 *   @OA\Property(property="username", type="string", maxLength=30),
 *   @OA\Property(property="bio", type="string", maxLength=500),
 *   @OA\Property(property="country", type="string", maxLength=2),
 *   @OA\Property(property="social_links", type="object"),
 *   @OA\Property(property="show_email", type="boolean"),
 *   @OA\Property(property="show_activity", type="boolean"),
 *   @OA\Property(property="show_match_history", type="boolean"),
 *   @OA\Property(property="show_stats", type="boolean"),
 * )
 */
class UpdateProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $userId = $this->user()->id;

        return [
            'name'    => ['sometimes', 'string', 'max:255'],
            'username' => [
                'sometimes',
                'string',
                'min:3',
                'max:30',
                'regex:/^[a-zA-Z0-9_]+$/',
                Rule::unique('users', 'username')->ignore($userId),
            ],
            'bio'     => ['sometimes', 'nullable', 'string', 'max:500'],
            'country' => ['sometimes', 'string', 'size:2'],
            'social_links'       => ['sometimes', 'nullable', 'array'],
            'social_links.twitter'  => ['sometimes', 'nullable', 'url', 'max:255'],
            'social_links.github'   => ['sometimes', 'nullable', 'url', 'max:255'],
            'social_links.linkedin' => ['sometimes', 'nullable', 'url', 'max:255'],
            'social_links.website'  => ['sometimes', 'nullable', 'url', 'max:255'],
            'show_email'         => ['sometimes', 'boolean'],
            'show_activity'      => ['sometimes', 'boolean'],
            'show_match_history' => ['sometimes', 'boolean'],
            'show_stats'         => ['sometimes', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'username.regex'  => 'Username may only contain letters, numbers, and underscores.',
            'country.size'    => 'Country must be a valid 2-letter ISO code.',
            'bio.max'         => 'Bio may not exceed 500 characters.',
        ];
    }
}
