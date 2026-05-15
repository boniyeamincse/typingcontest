<?php

namespace App\Policies;

use App\Models\User;

class RoleManagementPolicy
{
    public function manage(User $user): bool
    {
        return $user->hasRole('super_admin');
    }
}
