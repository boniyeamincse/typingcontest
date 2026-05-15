<?php

namespace App\Services\Admin;

use App\Models\User;
use App\Repositories\Admin\AdminUserRepositoryInterface;
use Illuminate\Support\Facades\DB;

class AdminUserService
{
    public function __construct(
        private readonly AdminUserRepositoryInterface $repository,
        private readonly AdminAuditService $auditService,
    ) {
    }

    public function list(array $filters)
    {
        return $this->repository->paginate($filters);
    }

    public function suspicious(array $filters)
    {
        return $this->repository->suspicious($filters);
    }

    public function ban(User $admin, User $target, string $reason): User
    {
        $target->forceFill([
            'is_banned' => true,
            'banned_reason' => $reason,
        ])->save();

        $this->auditService->log($admin, 'users', 'ban', $target, ['reason' => $reason]);

        return $target;
    }

    public function unban(User $admin, User $target): User
    {
        $target->forceFill([
            'is_banned' => false,
            'banned_reason' => null,
        ])->save();

        $this->auditService->log($admin, 'users', 'unban', $target);

        return $target;
    }

    public function suspend(User $admin, User $target, string $until, string $reason): User
    {
        $target->forceFill([
            'suspended_until' => $until,
            'banned_reason' => $reason,
        ])->save();

        $this->auditService->log($admin, 'users', 'suspend', $target, [
            'until' => $until,
            'reason' => $reason,
        ]);

        return $target;
    }

    public function resetPassword(User $admin, User $target, string $newPassword): void
    {
        DB::transaction(function () use ($admin, $target, $newPassword): void {
            $target->forceFill(['password' => $newPassword])->save();
            $target->tokens()->delete();
            $this->auditService->log($admin, 'users', 'reset_password', $target);
        });
    }
}
