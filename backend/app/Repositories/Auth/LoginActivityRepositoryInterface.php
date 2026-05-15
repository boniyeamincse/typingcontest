<?php

namespace App\Repositories\Auth;

use App\Models\LoginActivity;
use App\Models\User;

interface LoginActivityRepositoryInterface
{
    public function create(User $user, array $attributes): LoginActivity;
}
