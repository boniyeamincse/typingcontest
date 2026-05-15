<?php

namespace App\Events\Payment;

use App\Models\Payment;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PaymentConfirmed implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public readonly Payment $payment) {}

    public function broadcastOn(): array
    {
        return [new Channel('subscription.user.' . $this->payment->user_id)];
    }

    public function broadcastAs(): string
    {
        return 'payment.confirmed';
    }

    public function broadcastWith(): array
    {
        return [
            'payment_intent_id' => $this->payment->payment_intent_id,
            'gateway' => $this->payment->gateway,
            'status' => $this->payment->status,
            'amount' => (float) $this->payment->final_amount,
            'currency' => $this->payment->currency,
            'paid_at' => optional($this->payment->paid_at)->toISOString(),
        ];
    }
}
