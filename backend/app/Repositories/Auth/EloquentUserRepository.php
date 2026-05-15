<?php

namespace App\Repositories\Auth;

use App\Models\User;

class EloquentUserRepository implements UserRepositoryInterface
{
    public function create(array $attributes): User
    {
        return User::create($attributes);
    }

    public function findByEmail(string $email): ?User
    {
        return User::where('email', $email)->first();
    }

    public function findByEmailOrUsername(string $login): ?User
    {
        return User::where('email', $login)
            ->orWhere('username', $login)
            ->first();
    }

    public function updateLastLogin(User $user, string $ipAddress, string $userAgent): void
    {
        $user->forceFill([
            'last_login_at' => now(),
            'last_login_ip' => $ipAddress,
            'last_login_user_agent' => mb_substr($userAgent, 0, 500),
        ])->save();
    }
}
