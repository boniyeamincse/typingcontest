<?php

namespace App\Repositories\Profile;

use App\Models\User;
use App\Models\UserActivity;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface ActivityRepositoryInterface
{
    public function log(User $user, string $type, string $description, array $metadata = [], ?string $ip = null): UserActivity;

    public function getActivity(User $user, int $perPage = 20): LengthAwarePaginator;
}
