<?php

namespace App\Http\Controllers\API\Subscription;

use App\Http\Controllers\Controller;
use App\Services\Subscription\SubscriptionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SubscriptionController extends Controller
{
    public function __construct(private readonly SubscriptionService $subscriptionService) {}

    public function plans(): JsonResponse
    {
        $plans = $this->subscriptionService->listPlans()->map(function ($plan): array {
            return [
                'id' => $plan->id,
                'name' => $plan->name,
                'code' => $plan->code,
                'tier' => $plan->tier,
                'billing_cycle' => $plan->billing_cycle,
                'price' => (float) $plan->price,
                'currency' => $plan->currency,
                'duration_days' => $plan->duration_days,
                'features' => $plan->features ?? [],
            ];
        });

        return $this->success('Plans fetched successfully', $plans);
    }

    public function subscribe(Request $request): JsonResponse
    {
        $payload = $request->validate([
            'plan_code' => ['required', 'string', 'max:32'],
            'gateway' => ['required', 'in:bkash,nagad,sslcommerz'],
            'coupon_code' => ['nullable', 'string', 'max:64'],
        ]);

        $result = $this->subscriptionService->subscribe($request->user(), $payload);

        return $this->success('Subscription request created successfully', $result);
    }

    public function upgrade(Request $request): JsonResponse
    {
        $payload = $request->validate([
            'plan_code' => ['required', 'string', 'max:32'],
            'gateway' => ['required', 'in:bkash,nagad,sslcommerz'],
            'coupon_code' => ['nullable', 'string', 'max:64'],
        ]);

        $result = $this->subscriptionService->upgrade($request->user(), $payload);

        return $this->success('Subscription upgrade request created successfully', $result);
    }

    public function current(Request $request): JsonResponse
    {
        $current = $this->subscriptionService->getCurrent($request->user());

        return $this->success('Current subscription fetched successfully', $current);
    }

    public function cancel(Request $request): JsonResponse
    {
        $result = $this->subscriptionService->cancel($request->user());

        return $this->success('Subscription cancelled successfully', $result);
    }

    private function success(string $message, mixed $data): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $data,
        ]);
    }
}
