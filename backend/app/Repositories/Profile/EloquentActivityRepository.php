<?php

namespace App\Repositories\Profile;

use App\Models\User;
use App\Models\UserActivity;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class EloquentActivityRepository implements ActivityRepositoryInterface
{
    public function log(
        User $user,
        string $type,
        string $description,
        array $metadata = [],
        ?string $ip = null
    ): UserActivity {
        return UserActivity::create([
            'user_id'     => $user->id,
            'type'        => $type,
            'description' => $description,
            'metadata'    => empty($metadata) ? null : $metadata,
            'ip_address'  => $ip,
        ]);
    }

    public function getActivity(User $user, int $perPage = 20): LengthAwarePaginator
    {
        return $user->activities()
            ->orderByDesc('created_at')
            ->paginate($perPage);
    }
}
