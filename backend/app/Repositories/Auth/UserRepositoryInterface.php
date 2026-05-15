<?php

namespace App\Repositories\Auth;

use App\Models\User;

interface UserRepositoryInterface
{
    public function create(array $attributes): User;

    public function findByEmail(string $email): ?User;

    public function findByEmailOrUsername(string $login): ?User;

    public function updateLastLogin(User $user, string $ipAddress, string $userAgent): void;
}
