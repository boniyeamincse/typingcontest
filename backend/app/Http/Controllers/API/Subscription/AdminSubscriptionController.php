<?php

namespace App\Http\Controllers\API\Subscription;

use App\Http\Controllers\Controller;
use App\Models\CouponCode;
use App\Models\User;
use App\Services\Subscription\SubscriptionAdminService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminSubscriptionController extends Controller
{
    public function __construct(private readonly SubscriptionAdminService $subscriptionAdminService) {}

    public function coupons(Request $request): JsonResponse
    {
        $limit = max(1, min((int) $request->query('limit', 50), 200));
        $rows = $this->subscriptionAdminService->listCoupons($limit);

        return $this->success('Coupons fetched successfully', $rows);
    }

    public function createCoupon(Request $request): JsonResponse
    {
        $payload = $request->validate([
            'code' => ['required', 'string', 'max:64', 'unique:coupon_codes,code'],
            'discount_type' => ['required', 'in:percentage,fixed'],
            'discount_value' => ['required', 'numeric', 'min:0.01'],
            'max_discount' => ['nullable', 'numeric', 'min:0'],
            'usage_limit' => ['nullable', 'integer', 'min:1'],
            'per_user_limit' => ['nullable', 'integer', 'min:1'],
            'expires_at' => ['nullable', 'date'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $coupon = $this->subscriptionAdminService->createCoupon($payload);

        return $this->success('Coupon created successfully', $coupon, 201);
    }

    public function updateCoupon(Request $request, CouponCode $coupon): JsonResponse
    {
        $payload = $request->validate([
            'discount_type' => ['sometimes', 'in:percentage,fixed'],
            'discount_value' => ['sometimes', 'numeric', 'min:0.01'],
            'max_discount' => ['nullable', 'numeric', 'min:0'],
            'usage_limit' => ['nullable', 'integer', 'min:1'],
            'per_user_limit' => ['sometimes', 'integer', 'min:1'],
            'expires_at' => ['nullable', 'date'],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        $updated = $this->subscriptionAdminService->updateCoupon($coupon, $payload);

        return $this->success('Coupon updated successfully', $updated);
    }

    public function toggleCoupon(CouponCode $coupon): JsonResponse
    {
        $updated = $this->subscriptionAdminService->toggleCoupon($coupon);

        return $this->success('Coupon status updated successfully', $updated);
    }

    public function override(Request $request): JsonResponse
    {
        $payload = $request->validate([
            'user_id' => ['required', 'integer', 'exists:users,id'],
            'plan_code' => ['required', 'string', 'max:32'],
            'status' => ['nullable', 'in:pending,active,cancelled,expired,grace'],
            'auto_renew' => ['nullable', 'boolean'],
            'starts_at' => ['nullable', 'date'],
            'ends_at' => ['nullable', 'date', 'after_or_equal:starts_at'],
            'reason' => ['nullable', 'string', 'max:255'],
        ]);

        $user = User::query()->findOrFail($payload['user_id']);
        $subscription = $this->subscriptionAdminService->manualOverride($user, $payload);

        return $this->success('Subscription override applied successfully', $subscription);
    }

    private function success(string $message, mixed $data, int $status = 200): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $data,
        ], $status);
    }
}
