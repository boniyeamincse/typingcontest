<?php

namespace App\Services\Subscription;

use App\Models\CouponCode;
use App\Models\User;
use App\Models\UserSubscription;
use App\Repositories\Subscription\SubscriptionRepositoryInterface;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class SubscriptionAdminService
{
    public function __construct(private readonly SubscriptionRepositoryInterface $subscriptionRepository) {}

    public function listCoupons(int $limit = 50): Collection
    {
        return CouponCode::query()->latest('id')->limit($limit)->get();
    }

    public function createCoupon(array $payload): CouponCode
    {
        return CouponCode::query()->create([
            'code' => strtoupper($payload['code']),
            'discount_type' => $payload['discount_type'],
            'discount_value' => $payload['discount_value'],
            'max_discount' => $payload['max_discount'] ?? null,
            'usage_limit' => $payload['usage_limit'] ?? null,
            'per_user_limit' => $payload['per_user_limit'] ?? 1,
            'expires_at' => $payload['expires_at'] ?? null,
            'is_active' => (bool) ($payload['is_active'] ?? true),
        ]);
    }

    public function updateCoupon(CouponCode $coupon, array $payload): CouponCode
    {
        $coupon->update([
            'discount_type' => $payload['discount_type'] ?? $coupon->discount_type,
            'discount_value' => $payload['discount_value'] ?? $coupon->discount_value,
            'max_discount' => array_key_exists('max_discount', $payload) ? $payload['max_discount'] : $coupon->max_discount,
            'usage_limit' => array_key_exists('usage_limit', $payload) ? $payload['usage_limit'] : $coupon->usage_limit,
            'per_user_limit' => $payload['per_user_limit'] ?? $coupon->per_user_limit,
            'expires_at' => array_key_exists('expires_at', $payload) ? $payload['expires_at'] : $coupon->expires_at,
            'is_active' => array_key_exists('is_active', $payload) ? (bool) $payload['is_active'] : $coupon->is_active,
        ]);

        return $coupon->fresh();
    }

    public function toggleCoupon(CouponCode $coupon): CouponCode
    {
        $coupon->update(['is_active' => !$coupon->is_active]);

        return $coupon->fresh();
    }

    public function manualOverride(User $user, array $payload): UserSubscription
    {
        return DB::transaction(function () use ($user, $payload): UserSubscription {
            $plan = $this->subscriptionRepository->findPlanByCode($payload['plan_code']);
            if (!$plan) {
                throw ValidationException::withMessages(['plan_code' => ['Selected plan is invalid.']]);
            }

            $this->subscriptionRepository->expireActiveSubscriptions($user);

            $startsAt = isset($payload['starts_at']) ? now()->parse($payload['starts_at']) : now();
            $endsAt = isset($payload['ends_at'])
                ? now()->parse($payload['ends_at'])
                : ($plan->duration_days > 0 ? $startsAt->copy()->addDays((int) $plan->duration_days) : null);

            $status = $payload['status'] ?? 'active';

            $subscription = UserSubscription::query()->create([
                'user_id' => $user->id,
                'subscription_plan_id' => $plan->id,
                'status' => $status,
                'auto_renew' => (bool) ($payload['auto_renew'] ?? false),
                'starts_at' => $startsAt,
                'ends_at' => $endsAt,
                'grace_ends_at' => $endsAt ? $endsAt->copy()->addDays(3) : null,
                'meta' => [
                    'manual_override' => true,
                    'override_reason' => $payload['reason'] ?? null,
                ],
            ]);

            $user->forceFill([
                'plan_type' => in_array($status, ['active', 'grace'], true) ? $plan->tier : 'free',
                'subscription_status' => in_array($status, ['active', 'grace'], true) ? 'active' : 'expired',
                'subscription_end_date' => in_array($status, ['active', 'grace'], true) ? $endsAt : null,
            ])->save();

            Cache::forget("subscription:status:user:{$user->id}");

            return $subscription->fresh(['plan', 'user']);
        });
    }
}
