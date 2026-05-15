<?php

namespace App\Services\Payment;

use App\Models\Payment;
use App\Models\UserSubscription;
use App\Repositories\Payment\PaymentRepositoryInterface;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PaymentAdminService
{
    public function __construct(private readonly PaymentRepositoryInterface $paymentRepository) {}

    public function refundByIntent(string $paymentIntentId, ?string $reason = null): array
    {
        $payment = $this->paymentRepository->findByIntentId($paymentIntentId);

        if (!$payment) {
            throw ValidationException::withMessages(['payment_intent_id' => ['Payment not found.']]);
        }

        if ($payment->status !== 'paid') {
            throw ValidationException::withMessages(['payment' => ['Only paid payments can be refunded.']]);
        }

        $updated = DB::transaction(function () use ($payment, $reason): Payment {
            $payment->update([
                'status' => 'refunded',
                'meta' => array_merge($payment->meta ?? [], ['refund_reason' => $reason, 'refunded_at' => now()->toISOString()]),
            ]);

            $this->paymentRepository->addTransaction($payment, [
                'transaction_type' => 'refund',
                'status' => 'success',
                'gateway_transaction_id' => $payment->external_transaction_id,
                'request_payload' => ['reason' => $reason],
                'response_payload' => ['status' => 'refunded'],
                'processed_at' => now(),
                'message' => 'Refund processed by admin',
            ]);

            $payment->invoice()?->update(['status' => 'refunded']);

            $subscription = $payment->subscription;
            if ($subscription instanceof UserSubscription) {
                $subscription->update([
                    'status' => 'cancelled',
                    'cancelled_at' => now(),
                ]);

                $subscription->user?->forceFill([
                    'plan_type' => 'free',
                    'subscription_status' => 'active',
                    'subscription_end_date' => null,
                ])->save();

                if ($subscription->user_id) {
                    Cache::forget("subscription:status:user:{$subscription->user_id}");
                }
            }

            return $payment->fresh(['invoice', 'subscription.plan', 'transactions']);
        });

        return ['payment' => $updated];
    }
}
