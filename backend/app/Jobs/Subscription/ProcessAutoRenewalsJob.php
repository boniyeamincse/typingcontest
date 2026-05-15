<?php

namespace App\Jobs\Subscription;

use App\Models\Payment;
use App\Models\UserSubscription;
use App\Repositories\Payment\PaymentRepositoryInterface;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessAutoRenewalsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public string $queue = 'subscriptions';

    public function handle(PaymentRepositoryInterface $paymentRepository): void
    {
        UserSubscription::query()
            ->with(['user', 'plan'])
            ->where('status', 'active')
            ->where('auto_renew', true)
            ->whereNotNull('ends_at')
            ->whereBetween('ends_at', [now(), now()->addDay()])
            ->chunkById(100, function ($subscriptions) use ($paymentRepository): void {
                foreach ($subscriptions as $subscription) {
                    if (!$subscription->plan || (float) $subscription->plan->price <= 0.0) {
                        continue;
                    }

                    $exists = Payment::query()
                        ->where('user_subscription_id', $subscription->id)
                        ->whereIn('status', ['pending', 'processing'])
                        ->exists();

                    if ($exists) {
                        continue;
                    }

                    $paymentRepository->createPayment($subscription->user, $subscription, [
                        'gateway' => 'sslcommerz',
                        'amount' => (float) $subscription->plan->price,
                        'discount_amount' => 0,
                        'final_amount' => (float) $subscription->plan->price,
                        'currency' => $subscription->plan->currency,
                        'meta' => ['flow' => 'auto_renewal'],
                    ]);
                }
            });
    }
}
