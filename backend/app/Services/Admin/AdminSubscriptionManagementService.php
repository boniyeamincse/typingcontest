<?php

namespace App\Services\Admin;

use App\Models\SubscriptionPlan;
use App\Models\User;
use App\Models\UserSubscription;

class AdminSubscriptionManagementService
{
    public function activeSubscriptions(array $filters)
    {
        return UserSubscription::query()
            ->with(['user:id,name,email,plan_type', 'plan:id,code,name,price'])
            ->when($filters['status'] ?? null, fn ($q, $status) => $q->where('status', $status))
            ->latest('id')
            ->paginate(max(10, min(100, (int) ($filters['per_page'] ?? 20))));
    }

    public function managePlans(): array
    {
        return SubscriptionPlan::query()->orderBy('sort_order')->get()->all();
    }

    public function upgradeDowngrade(User $user, string $planType): User
    {
        $user->update(['plan_type' => $planType]);

        return $user->refresh();
    }

    public function cancel(UserSubscription $subscription, ?string $reason): UserSubscription
    {
        $subscription->update([
            'status' => 'cancelled',
            'ends_at' => now(),
            'cancel_reason' => $reason,
        ]);

        return $subscription->refresh();
    }
}
