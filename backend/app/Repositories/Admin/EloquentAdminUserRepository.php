<?php

namespace App\Repositories\Admin;

use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class EloquentAdminUserRepository implements AdminUserRepositoryInterface
{
    public function paginate(array $filters): LengthAwarePaginator
    {
        $perPage = max(10, min(100, (int) ($filters['per_page'] ?? 20)));

        return User::query()
            ->when($filters['plan'] ?? null, fn ($q, $plan) => $q->where('plan_type', $plan))
            ->when($filters['search'] ?? null, function ($q, $search): void {
                $q->where(function ($x) use ($search): void {
                    $x->where('name', 'like', "%{$search}%")
                        ->orWhere('username', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->latest('id')
            ->paginate($perPage);
    }

    public function suspicious(array $filters): LengthAwarePaginator
    {
        $perPage = max(10, min(100, (int) ($filters['per_page'] ?? 20)));

        return User::query()
            ->where(function ($q): void {
                $q->where('is_banned', true)
                    ->orWhereNotNull('suspended_until')
                    ->orWhereNotNull('last_login_ip');
            })
            ->when($filters['country'] ?? null, fn ($q, $country) => $q->where('country', $country))
            ->latest('id')
            ->paginate($perPage);
    }
}
