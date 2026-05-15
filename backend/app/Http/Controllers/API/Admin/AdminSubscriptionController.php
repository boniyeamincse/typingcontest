<?php

namespace App\Http\Controllers\API\Admin;

use App\Http\Controllers\API\Admin\Concerns\RespondsWithApi;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserSubscription;
use App\Services\Admin\AdminSubscriptionManagementService;
use Illuminate\Http\Request;

class AdminSubscriptionController extends Controller
{
    use RespondsWithApi;

    public function __construct(private readonly AdminSubscriptionManagementService $service)
    {
    }

    public function index(Request $request)
    {
        return $this->success('Action completed successfully', $this->service->activeSubscriptions($request->validate([
            'status' => ['nullable', 'string'],
            'per_page' => ['nullable', 'integer', 'min:10', 'max:100'],
        ])));
    }

    public function plans()
    {
        return $this->success('Action completed successfully', $this->service->managePlans());
    }

    public function upgradeDowngrade(Request $request)
    {
        $payload = $request->validate([
            'user_id' => ['required', 'integer', 'exists:users,id'],
            'plan_type' => ['required', 'string', 'in:free,pro,vip'],
        ]);

        $user = User::query()->findOrFail($payload['user_id']);

        return $this->success('Action completed successfully', $this->service->upgradeDowngrade($user, $payload['plan_type']));
    }

    public function cancel(Request $request)
    {
        $payload = $request->validate([
            'subscription_id' => ['required', 'integer', 'exists:user_subscriptions,id'],
            'reason' => ['nullable', 'string', 'max:255'],
        ]);

        $subscription = UserSubscription::query()->findOrFail($payload['subscription_id']);

        return $this->success('Action completed successfully', $this->service->cancel($subscription, $payload['reason'] ?? null));
    }
}
