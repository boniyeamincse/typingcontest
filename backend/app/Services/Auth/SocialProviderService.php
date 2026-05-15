<?php

namespace App\Services\Auth;

use Illuminate\Support\Facades\Http;
use Illuminate\Validation\ValidationException;

class SocialProviderService
{
    public function fetchGoogleUser(string $accessToken): array
    {
        $response = Http::timeout(10)
            ->withToken($accessToken)
            ->acceptJson()
            ->get('https://www.googleapis.com/oauth2/v3/userinfo');

        if (! $response->successful()) {
            throw ValidationException::withMessages([
                'access_token' => ['Invalid Google access token.'],
            ]);
        }

        $payload = $response->json();

        return [
            'provider_user_id' => (string) ($payload['sub'] ?? ''),
            'name' => $payload['name'] ?? null,
            'email' => $payload['email'] ?? null,
            'avatar' => $payload['picture'] ?? null,
        ];
    }

    public function fetchGithubUser(string $accessToken): array
    {
        $userResponse = Http::timeout(10)
            ->withToken($accessToken)
            ->acceptJson()
            ->get('https://api.github.com/user');

        if (! $userResponse->successful()) {
            throw ValidationException::withMessages([
                'access_token' => ['Invalid GitHub access token.'],
            ]);
        }

        $userPayload = $userResponse->json();

        $emailResponse = Http::timeout(10)
            ->withToken($accessToken)
            ->acceptJson()
            ->get('https://api.github.com/user/emails');

        $email = null;

        if ($emailResponse->successful()) {
            $emails = $emailResponse->json();
            $primary = collect($emails)->firstWhere('primary', true);
            $email = $primary['email'] ?? null;
        }

        return [
            'provider_user_id' => (string) ($userPayload['id'] ?? ''),
            'name' => $userPayload['name'] ?? $userPayload['login'] ?? null,
            'email' => $email,
            'avatar' => $userPayload['avatar_url'] ?? null,
        ];
    }
}
