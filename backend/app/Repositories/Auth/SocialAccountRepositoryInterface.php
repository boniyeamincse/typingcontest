<?php

namespace App\Repositories\Auth;

use App\Models\SocialAccount;
use App\Models\User;

interface SocialAccountRepositoryInterface
{
    public function findByProviderAndProviderId(string $provider, string $providerUserId): ?SocialAccount;

    public function updateOrCreateForUser(User $user, string $provider, array $data): SocialAccount;
}
