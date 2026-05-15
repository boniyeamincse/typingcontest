<?php

namespace App\Repositories\Subscription;

use App\Models\SubscriptionPlan;
use App\Models\User;
use App\Models\UserSubscription;
use Illuminate\Support\Collection;

class EloquentSubscriptionRepository implements SubscriptionRepositoryInterface
{
    public function getActivePlans(): Collection
    {
        return SubscriptionPlan::query()
            ->where('is_active', true)
            ->orderByRaw("FIELD(tier, 'free', 'pro', 'vip')")
            ->orderBy('price')
            ->get();
    }

    public function findPlanByCode(string $code): ?SubscriptionPlan
    {
        return SubscriptionPlan::query()
            ->where('code', $code)
            ->where('is_active', true)
            ->first();
    }

    public function getCurrentSubscription(User $user): ?UserSubscription
    {
        return UserSubscription::query()
            ->with('plan')
            ->where('user_id', $user->id)
            ->whereIn('status', ['active', 'grace', 'pending'])
            ->latest('id')
            ->first();
    }

    public function expireActiveSubscriptions(User $user): void
    {
        UserSubscription::query()
            ->where('user_id', $user->id)
            ->whereIn('status', ['active', 'grace'])
            ->update([
                'status' => 'expired',
                'expired_at' => now(),
            ]);
    }

    public function createPendingSubscription(User $user, SubscriptionPlan $plan, ?int $couponCodeId = null): UserSubscription
    {
        return UserSubscription::query()->create([
            'user_id' => $user->id,
            'subscription_plan_id' => $plan->id,
            'coupon_code_id' => $couponCodeId,
            'status' => 'pending',
            'auto_renew' => true,
            'meta' => ['plan_code' => $plan->code],
        ]);
    }

    public function activateSubscription(UserSubscription $subscription): UserSubscription
    {
        $startsAt = now();
        $endsAt = $subscription->plan->duration_days > 0
            ? now()->addDays((int) $subscription->plan->duration_days)
            : null;

        $subscription->update([
            'status' => 'active',
            'starts_at' => $startsAt,
            'ends_at' => $endsAt,
            'grace_ends_at' => $endsAt ? (clone $endsAt)->addDays(3) : null,
            'expired_at' => null,
        ]);

        return $subscription->fresh(['plan']);
    }

    public function cancelSubscription(UserSubscription $subscription): UserSubscription
    {
        $subscription->update([
            'auto_renew' => false,
            'status' => 'cancelled',
            'cancelled_at' => now(),
        ]);

        return $subscription->fresh(['plan']);
    }

    public function markSubscriptionExpired(UserSubscription $subscription): UserSubscription
    {
        $subscription->update([
            'status' => 'expired',
            'expired_at' => now(),
        ]);

        return $subscription->fresh(['plan']);
    }
}
