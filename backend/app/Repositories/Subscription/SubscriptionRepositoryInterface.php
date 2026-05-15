<?php

namespace App\Repositories\Subscription;

use App\Models\SubscriptionPlan;
use App\Models\User;
use App\Models\UserSubscription;
use Illuminate\Support\Collection;

interface SubscriptionRepositoryInterface
{
    public function getActivePlans(): Collection;

    public function findPlanByCode(string $code): ?SubscriptionPlan;

    public function getCurrentSubscription(User $user): ?UserSubscription;

    public function expireActiveSubscriptions(User $user): void;

    public function createPendingSubscription(User $user, SubscriptionPlan $plan, ?int $couponCodeId = null): UserSubscription;

    public function activateSubscription(UserSubscription $subscription): UserSubscription;

    public function cancelSubscription(UserSubscription $subscription): UserSubscription;

    public function markSubscriptionExpired(UserSubscription $subscription): UserSubscription;
}
