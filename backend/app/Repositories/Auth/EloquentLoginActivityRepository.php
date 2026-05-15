<?php

namespace App\Repositories\Auth;

use App\Models\LoginActivity;
use App\Models\User;

class EloquentLoginActivityRepository implements LoginActivityRepositoryInterface
{
    public function create(User $user, array $attributes): LoginActivity
    {
        return LoginActivity::create([
            'user_id' => $user->id,
            'login_identifier' => $attributes['login_identifier'] ?? null,
            'ip_address' => $attributes['ip_address'] ?? null,
            'user_agent' => $attributes['user_agent'] ?? null,
            'device_name' => $attributes['device_name'] ?? 'unknown-device',
            'status' => $attributes['status'] ?? 'success',
            'logged_in_at' => $attributes['logged_in_at'] ?? now(),
        ]);
    }
}
