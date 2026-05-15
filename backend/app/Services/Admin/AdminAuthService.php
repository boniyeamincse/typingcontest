<?php

namespace App\Services\Admin;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AdminAuthService
{
    public function login(string $email, string $password, ?string $deviceName = null): array
    {
        $user = User::query()->where('email', $email)->first();

        if (! $user || ! Hash::check($password, $user->password)) {
            throw ValidationException::withMessages(['email' => ['Invalid credentials.']]);
        }

        if (! $user->hasAnyRole(['super_admin', 'contest_admin', 'user_moderator', 'support_admin', 'content_manager', 'admin'])) {
            throw ValidationException::withMessages(['email' => ['Admin access is required.']]);
        }

        $token = $user->createToken($deviceName ?: 'admin-dashboard-token')->plainTextToken;

        return [
            'token' => $token,
            'user' => $user->load('roles:id,name'),
        ];
    }

    public function logout(User $user): void
    {
        $user->currentAccessToken()?->delete();
    }
}
