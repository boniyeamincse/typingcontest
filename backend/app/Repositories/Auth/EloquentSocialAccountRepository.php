<?php

namespace App\Repositories\Auth;

use App\Models\SocialAccount;
use App\Models\User;

class EloquentSocialAccountRepository implements SocialAccountRepositoryInterface
{
    public function findByProviderAndProviderId(string $provider, string $providerUserId): ?SocialAccount
    {
        return SocialAccount::where('provider', $provider)
            ->where('provider_user_id', $providerUserId)
            ->first();
    }

    public function updateOrCreateForUser(User $user, string $provider, array $data): SocialAccount
    {
        return SocialAccount::updateOrCreate(
            [
                'user_id' => $user->id,
                'provider' => $provider,
                'provider_user_id' => $data['provider_user_id'],
            ],
            [
                'provider_email' => $data['provider_email'] ?? null,
                'provider_avatar' => $data['provider_avatar'] ?? null,
                'access_token' => $data['access_token'] ?? null,
                'refresh_token' => $data['refresh_token'] ?? null,
                'token_expires_at' => $data['token_expires_at'] ?? null,
            ]
        );
    }
}
