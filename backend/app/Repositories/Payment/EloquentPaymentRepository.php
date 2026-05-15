<?php

namespace App\Repositories\Payment;

use App\Models\Invoice;
use App\Models\Payment;
use App\Models\User;
use App\Models\UserSubscription;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class EloquentPaymentRepository implements PaymentRepositoryInterface
{
    public function createPayment(User $user, UserSubscription $subscription, array $attributes): Payment
    {
        return Payment::query()->create(array_merge($attributes, [
            'user_id' => $user->id,
            'user_subscription_id' => $subscription->id,
            'subscription_plan_id' => $subscription->subscription_plan_id,
            'payment_intent_id' => $attributes['payment_intent_id'] ?? 'pi_' . Str::upper(Str::random(24)),
            'status' => 'pending',
        ]));
    }

    public function findByIntentId(string $intentId): ?Payment
    {
        return Payment::query()->with(['subscription.plan', 'user', 'coupon'])->where('payment_intent_id', $intentId)->first();
    }

    public function findPaidByExternalTransaction(string $gateway, string $externalTransactionId): ?Payment
    {
        return Payment::query()
            ->where('gateway', $gateway)
            ->where('external_transaction_id', $externalTransactionId)
            ->where('status', 'paid')
            ->first();
    }

    public function markPaid(Payment $payment, array $payload): Payment
    {
        $payment->update([
            'status' => 'paid',
            'paid_at' => now(),
            'gateway_reference' => $payload['gateway_reference'] ?? $payment->gateway_reference,
            'external_transaction_id' => $payload['external_transaction_id'] ?? $payment->external_transaction_id,
            'meta' => array_merge($payment->meta ?? [], ['verified_payload' => $payload]),
        ]);

        return $payment->fresh(['subscription.plan', 'user', 'coupon']);
    }

    public function markFailed(Payment $payment, string $reason, array $payload = []): Payment
    {
        $payment->update([
            'status' => 'failed',
            'failed_at' => now(),
            'failure_reason' => $reason,
            'meta' => array_merge($payment->meta ?? [], ['failure_payload' => $payload]),
        ]);

        return $payment->fresh();
    }

    public function addTransaction(Payment $payment, array $attributes): void
    {
        $payment->transactions()->create($attributes);
    }

    public function createInvoice(Payment $payment): Invoice
    {
        return Invoice::query()->firstOrCreate(
            ['payment_id' => $payment->id],
            [
                'user_id' => $payment->user_id,
                'user_subscription_id' => $payment->user_subscription_id,
                'invoice_number' => 'INV-' . now()->format('Ymd') . '-' . Str::upper(Str::random(8)),
                'subtotal' => $payment->amount,
                'discount' => $payment->discount_amount,
                'total' => $payment->final_amount,
                'currency' => $payment->currency,
                'status' => $payment->status === 'paid' ? 'paid' : 'pending',
                'issued_at' => now(),
                'paid_at' => $payment->paid_at,
                'billing_snapshot' => [
                    'gateway' => $payment->gateway,
                    'intent' => $payment->payment_intent_id,
                    'plan_id' => $payment->subscription_plan_id,
                ],
            ]
        );
    }

    public function getHistory(User $user, int $limit = 20): Collection
    {
        return Payment::query()
            ->with(['plan:id,name,code,tier,billing_cycle', 'transactions:id,payment_id,transaction_type,status,processed_at'])
            ->where('user_id', $user->id)
            ->latest('id')
            ->limit($limit)
            ->get();
    }

    public function countCouponUsageForUser(int $couponCodeId, int $userId): int
    {
        return Payment::query()
            ->where('coupon_code_id', $couponCodeId)
            ->where('user_id', $userId)
            ->where('status', 'paid')
            ->count();
    }

    public function incrementCouponUsage(int $couponCodeId): void
    {
        \App\Models\CouponCode::query()->whereKey($couponCodeId)->increment('used_count');
    }
}
