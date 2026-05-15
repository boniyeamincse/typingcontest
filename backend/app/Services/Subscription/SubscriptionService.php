<?php

namespace App\Services\Subscription;

use App\Events\Subscription\SubscriptionActivated;
use App\Events\Subscription\SubscriptionUpgraded;
use App\Models\CouponCode;
use App\Models\User;
use App\Repositories\Payment\PaymentRepositoryInterface;
use App\Repositories\Subscription\SubscriptionRepositoryInterface;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\Models\Role;

class SubscriptionService
{
    public function __construct(
        private readonly SubscriptionRepositoryInterface $subscriptionRepository,
        private readonly PaymentRepositoryInterface $paymentRepository,
    ) {}

    public function listPlans()
    {
        return $this->subscriptionRepository->getActivePlans();
    }

    public function subscribe(User $user, array $payload): array
    {
        return DB::transaction(function () use ($user, $payload): array {
            $plan = $this->subscriptionRepository->findPlanByCode($payload['plan_code']);
            if (!$plan) {
                throw ValidationException::withMessages(['plan_code' => ['Selected plan is invalid.']]);
            }

            $coupon = $this->resolveCoupon($user, $payload['coupon_code'] ?? null);

            $this->subscriptionRepository->expireActiveSubscriptions($user);
            $subscription = $this->subscriptionRepository->createPendingSubscription($user, $plan, $coupon?->id);

            if ((float) $plan->price <= 0.0) {
                $active = $this->subscriptionRepository->activateSubscription($subscription);
                $this->syncUserSubscriptionProfile($user, $active->plan->tier, $active->ends_at);
                Cache::forget($this->subscriptionCacheKey($user->id));
                $this->safeBroadcast(new SubscriptionActivated($active));

                return [
                    'subscription' => $active,
                    'payment_required' => false,
                    'payment' => null,
                ];
            }

            [$discountAmount, $finalAmount] = $this->computeDiscount((float) $plan->price, $coupon);

            $payment = $this->paymentRepository->createPayment($user, $subscription, [
                'gateway' => $payload['gateway'],
                'amount' => (float) $plan->price,
                'discount_amount' => $discountAmount,
                'final_amount' => $finalAmount,
                'currency' => $plan->currency,
                'coupon_code_id' => $coupon?->id,
                'meta' => ['flow' => 'subscribe'],
            ]);

            return [
                'subscription' => $subscription->fresh(['plan']),
                'payment_required' => true,
                'payment' => $payment,
            ];
        });
    }

    public function upgrade(User $user, array $payload): array
    {
        $current = $this->getCurrent($user);
        $currentTier = $current['tier'] ?? 'free';

        $targetPlan = $this->subscriptionRepository->findPlanByCode($payload['plan_code']);
        if (!$targetPlan) {
            throw ValidationException::withMessages(['plan_code' => ['Selected plan is invalid.']]);
        }

        if ($this->tierWeight($targetPlan->tier) <= $this->tierWeight($currentTier)) {
            throw ValidationException::withMessages(['plan_code' => ['Upgrade requires a higher tier plan.']]);
        }

        $result = $this->subscribe($user, $payload);
        if (!$result['payment_required']) {
            event(new SubscriptionUpgraded($result['subscription']));
        }

        return $result;
    }

    public function cancel(User $user): array
    {
        $current = $this->subscriptionRepository->getCurrentSubscription($user);

        if (!$current || !in_array($current->status, ['active', 'grace', 'pending'], true)) {
            throw ValidationException::withMessages(['subscription' => ['No cancellable subscription found.']]);
        }

        $cancelled = $this->subscriptionRepository->cancelSubscription($current);

        return [
            'subscription' => $cancelled,
        ];
    }

    public function getCurrent(User $user): array
    {
        $cached = Cache::get($this->subscriptionCacheKey($user->id));
        if (is_array($cached)) {
            return $cached;
        }

        $current = $this->subscriptionRepository->getCurrentSubscription($user);

        if ($current && $current->status === 'active' && $current->ends_at && now()->greaterThan($current->ends_at)) {
            if ($current->grace_ends_at && now()->lessThanOrEqualTo($current->grace_ends_at)) {
                $current->update(['status' => 'grace']);
            } else {
                $expired = $this->subscriptionRepository->markSubscriptionExpired($current);
                $this->syncUserSubscriptionProfile($user, 'free', null);
                $data = $this->buildCurrentPayload($expired, 'free');
                Cache::put($this->subscriptionCacheKey($user->id), $data, 300);

                return $data;
            }
        }

        if (!$current) {
            $data = $this->buildFreePayload();
            Cache::put($this->subscriptionCacheKey($user->id), $data, 300);

            return $data;
        }

        $data = $this->buildCurrentPayload($current, $current->plan?->tier ?? 'free');
        Cache::put($this->subscriptionCacheKey($user->id), $data, 300);

        return $data;
    }

    public function syncAfterPayment(User $user, \App\Models\UserSubscription $subscription): void
    {
        $active = $this->subscriptionRepository->activateSubscription($subscription);
        $tier = $active->plan?->tier ?? 'free';

        $this->syncUserSubscriptionProfile($user, $tier, $active->ends_at);
        Cache::forget($this->subscriptionCacheKey($user->id));
        $this->safeBroadcast(new SubscriptionActivated($active));

        if ($tier !== 'free') {
            $this->safeBroadcast(new SubscriptionUpgraded($active));
        }
    }

    private function resolveCoupon(User $user, ?string $couponCode): ?CouponCode
    {
        if (!$couponCode) {
            return null;
        }

        $coupon = CouponCode::query()
            ->where('code', strtoupper($couponCode))
            ->where('is_active', true)
            ->first();

        if (!$coupon) {
            throw ValidationException::withMessages(['coupon_code' => ['Coupon code is invalid.']]);
        }

        if ($coupon->expires_at && now()->greaterThan($coupon->expires_at)) {
            throw ValidationException::withMessages(['coupon_code' => ['Coupon code has expired.']]);
        }

        if ($coupon->usage_limit !== null && $coupon->used_count >= $coupon->usage_limit) {
            throw ValidationException::withMessages(['coupon_code' => ['Coupon usage limit reached.']]);
        }

        $userUsage = $this->paymentRepository->countCouponUsageForUser($coupon->id, $user->id);
        if ($userUsage >= $coupon->per_user_limit) {
            throw ValidationException::withMessages(['coupon_code' => ['Coupon per-user limit reached.']]);
        }

        return $coupon;
    }

    private function computeDiscount(float $price, ?CouponCode $coupon): array
    {
        if (!$coupon) {
            return [0.0, $price];
        }

        $discount = $coupon->discount_type === 'percentage'
            ? ($price * ((float) $coupon->discount_value / 100))
            : (float) $coupon->discount_value;

        if ($coupon->max_discount !== null) {
            $discount = min($discount, (float) $coupon->max_discount);
        }

        $discount = round(max(0, $discount), 2);
        $final = round(max(0, $price - $discount), 2);

        return [$discount, $final];
    }

    private function syncUserSubscriptionProfile(User $user, string $tier, $endsAt): void
    {
        $user->forceFill([
            'plan_type' => $tier,
            'subscription_status' => $tier === 'free' ? 'active' : 'active',
            'subscription_end_date' => $endsAt,
        ])->save();

        Role::findOrCreate('free_user', 'api');
        Role::findOrCreate('pro_user', 'api');
        Role::findOrCreate('vip_user', 'api');

        $role = match ($tier) {
            'vip' => 'vip_user',
            'pro' => 'pro_user',
            default => 'free_user',
        };

        $user->syncRoles([$role]);
    }

    private function subscriptionCacheKey(int $userId): string
    {
        return "subscription:status:user:{$userId}";
    }

    private function tierWeight(string $tier): int
    {
        return match ($tier) {
            'vip' => 3,
            'pro' => 2,
            default => 1,
        };
    }

    private function buildFreePayload(): array
    {
        return [
            'tier' => 'free',
            'status' => 'active',
            'plan' => [
                'code' => 'free_monthly',
                'name' => 'Free Plan',
                'billing_cycle' => 'monthly',
                'price' => 0,
            ],
            'feature_access' => $this->featureAccessForTier('free'),
            'expires_at' => null,
            'grace_ends_at' => null,
        ];
    }

    private function buildCurrentPayload(\App\Models\UserSubscription $subscription, string $tier): array
    {
        return [
            'tier' => $tier,
            'status' => $subscription->status,
            'plan' => [
                'code' => $subscription->plan?->code,
                'name' => $subscription->plan?->name,
                'billing_cycle' => $subscription->plan?->billing_cycle,
                'price' => (float) ($subscription->plan?->price ?? 0),
            ],
            'feature_access' => $this->featureAccessForTier($tier),
            'expires_at' => optional($subscription->ends_at)->toISOString(),
            'grace_ends_at' => optional($subscription->grace_ends_at)->toISOString(),
        ];
    }

    private function featureAccessForTier(string $tier): array
    {
        $map = [
            'contest' => in_array($tier, ['pro', 'vip'], true),
            'analytics' => in_array($tier, ['pro', 'vip'], true),
            'multiplayer' => in_array($tier, ['pro', 'vip'], true),
            'ai_features' => $tier === 'vip',
            'premium_leaderboard' => in_array($tier, ['pro', 'vip'], true),
            'vip_leaderboard' => $tier === 'vip',
            'ads_enabled' => $tier === 'free',
        ];

        return $map;
    }

    private function safeBroadcast(object $event): void
    {
        if (config('broadcasting.default') === 'redis' && !class_exists(\Redis::class)) {
            return;
        }

        try {
            event($event);
        } catch (\Throwable $exception) {
            report($exception);
        }
    }
}
