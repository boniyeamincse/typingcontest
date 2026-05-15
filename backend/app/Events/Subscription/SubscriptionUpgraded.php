<?php

namespace App\Events\Subscription;

use App\Models\UserSubscription;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class SubscriptionUpgraded implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public readonly UserSubscription $subscription) {}

    public function broadcastOn(): array
    {
        return [new Channel('subscription.user.' . $this->subscription->user_id)];
    }

    public function broadcastAs(): string
    {
        return 'subscription.upgraded';
    }

    public function broadcastWith(): array
    {
        return [
            'subscription_id' => $this->subscription->id,
            'plan_code' => $this->subscription->plan?->code,
            'tier' => $this->subscription->plan?->tier,
            'status' => $this->subscription->status,
        ];
    }
}
