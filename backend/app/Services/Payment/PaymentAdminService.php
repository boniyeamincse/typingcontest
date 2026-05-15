<?php

namespace App\Services\Payment;

use App\Models\Payment;
use App\Models\UserSubscription;
use App\Repositories\Payment\PaymentRepositoryInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PaymentAdminService
{
    public function __construct(private readonly PaymentRepositoryInterface $paymentRepository) {}

    public function report(array $filters): array
    {
        $limit = max(1, min((int) ($filters['limit'] ?? 50), 200));
        $baseQuery = $this->buildReportQuery($filters);

        $rows = (clone $baseQuery)
            ->latest('id')
            ->limit($limit)
            ->get();

        $stats = (clone $baseQuery)
            ->selectRaw('COUNT(*) as total_transactions')
            ->selectRaw('SUM(final_amount) as total_amount')
            ->selectRaw('SUM(CASE WHEN status = "paid" THEN final_amount ELSE 0 END) as paid_amount')
            ->selectRaw('SUM(CASE WHEN status = "refunded" THEN final_amount ELSE 0 END) as refunded_amount')
            ->selectRaw('SUM(CASE WHEN status = "failed" THEN final_amount ELSE 0 END) as failed_amount')
            ->first();

        return [
            'filters' => [
                'from_date' => $filters['from_date'] ?? null,
                'to_date' => $filters['to_date'] ?? null,
                'gateway' => $filters['gateway'] ?? null,
                'payment_status' => $filters['payment_status'] ?? null,
                'subscription_status' => $filters['subscription_status'] ?? null,
                'coupon_code' => $filters['coupon_code'] ?? null,
                'refund_reason' => $filters['refund_reason'] ?? null,
                'plan_code' => $filters['plan_code'] ?? null,
                'user_id' => $filters['user_id'] ?? null,
                'limit' => $limit,
            ],
            'summary' => [
                'total_transactions' => (int) ($stats?->total_transactions ?? 0),
                'total_amount' => round((float) ($stats?->total_amount ?? 0), 2),
                'paid_amount' => round((float) ($stats?->paid_amount ?? 0), 2),
                'refunded_amount' => round((float) ($stats?->refunded_amount ?? 0), 2),
                'failed_amount' => round((float) ($stats?->failed_amount ?? 0), 2),
                'net_amount' => round(((float) ($stats?->paid_amount ?? 0)) - ((float) ($stats?->refunded_amount ?? 0)), 2),
            ],
            'rows' => $rows,
        ];
    }

    public function exportCsv(array $filters): array
    {
        $limit = max(1, min((int) ($filters['limit'] ?? 1000), 5000));
        $query = $this->buildReportQuery($filters);

        $rows = (clone $query)
            ->latest('id')
            ->limit($limit)
            ->get();

        $stream = fopen('php://temp', 'r+');

        fputcsv($stream, [
            'payment_intent_id',
            'gateway',
            'payment_status',
            'subscription_status',
            'plan_code',
            'coupon_code',
            'amount',
            'discount_amount',
            'final_amount',
            'currency',
            'refund_reason',
            'user_id',
            'username',
            'user_email',
            'country',
            'invoice_number',
            'created_at',
            'paid_at',
        ]);

        foreach ($rows as $row) {
            fputcsv($stream, [
                $row->payment_intent_id,
                $row->gateway,
                $row->status,
                $row->subscription?->status,
                $row->plan?->code,
                $row->coupon?->code,
                (float) $row->amount,
                (float) $row->discount_amount,
                (float) $row->final_amount,
                $row->currency,
                $row->meta['refund_reason'] ?? null,
                $row->user?->id,
                $row->user?->username,
                $row->user?->email,
                $row->user?->country,
                $row->invoice?->invoice_number,
                optional($row->created_at)->toISOString(),
                optional($row->paid_at)->toISOString(),
            ]);
        }

        rewind($stream);
        $content = stream_get_contents($stream) ?: '';
        fclose($stream);

        return [
            'filename' => 'subscriptions_payments_report_' . now()->format('Ymd_His') . '.csv',
            'content' => $content,
        ];
    }

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

    private function buildReportQuery(array $filters): Builder
    {
        $query = Payment::query()
            ->with([
                'user:id,username,email,country',
                'plan:id,code,name,tier,billing_cycle',
                'coupon:id,code',
                'subscription:id,user_id,subscription_plan_id,status,starts_at,ends_at',
                'invoice:id,payment_id,invoice_number,status,total,currency,issued_at,paid_at',
            ]);

        $this->applyFilters($query, $filters);

        return $query;
    }

    private function applyFilters(Builder $query, array $filters): void
    {
        if (!empty($filters['from_date'])) {
            $query->whereDate('payments.created_at', '>=', $filters['from_date']);
        }

        if (!empty($filters['to_date'])) {
            $query->whereDate('payments.created_at', '<=', $filters['to_date']);
        }

        if (!empty($filters['gateway'])) {
            $query->where('payments.gateway', $filters['gateway']);
        }

        if (!empty($filters['payment_status'])) {
            $query->where('payments.status', $filters['payment_status']);
        }

        if (!empty($filters['coupon_code'])) {
            $query->whereHas('coupon', function (Builder $q) use ($filters): void {
                $q->where('code', strtoupper((string) $filters['coupon_code']));
            });
        }

        if (!empty($filters['subscription_status'])) {
            $query->whereHas('subscription', function (Builder $q) use ($filters): void {
                $q->where('status', (string) $filters['subscription_status']);
            });
        }

        if (!empty($filters['plan_code'])) {
            $query->whereHas('plan', function (Builder $q) use ($filters): void {
                $q->where('code', (string) $filters['plan_code']);
            });
        }

        if (!empty($filters['refund_reason'])) {
            $query->where('payments.meta->refund_reason', 'like', '%' . $filters['refund_reason'] . '%');
        }

        if (!empty($filters['user_id'])) {
            $query->where('payments.user_id', (int) $filters['user_id']);
        }
    }
}
