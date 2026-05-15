<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    public function view(User $authUser, User $targetUser): bool
    {
        return $authUser->id === $targetUser->id || $authUser->hasRole('admin');
    }

    public function update(User $authUser, User $targetUser): bool
    {
        return $authUser->id === $targetUser->id || $authUser->hasRole('admin');
    }

    public function delete(User $authUser, User $targetUser): bool
    {
        return $authUser->hasRole('admin') && $authUser->id !== $targetUser->id;
    }
}
