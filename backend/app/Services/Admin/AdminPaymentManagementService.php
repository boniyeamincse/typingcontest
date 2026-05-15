<?php

namespace App\Services\Admin;

use App\Models\Payment;

class AdminPaymentManagementService
{
    public function list(array $filters)
    {
        return Payment::query()
            ->with(['user:id,name,email', 'plan:id,code,name', 'invoice:id,payment_id,invoice_no,total'])
            ->when($filters['status'] ?? null, fn ($q, $status) => $q->where('status', $status))
            ->when($filters['gateway'] ?? null, fn ($q, $gateway) => $q->where('gateway', $gateway))
            ->latest('id')
            ->paginate(max(10, min(100, (int) ($filters['per_page'] ?? 20))));
    }

    public function verify(Payment $payment): Payment
    {
        $payment->update([
            'status' => 'paid',
            'paid_at' => now(),
        ]);

        return $payment->refresh();
    }

    public function refund(Payment $payment, string $reason): Payment
    {
        $meta = (array) ($payment->meta ?? []);
        $meta['refund_reason'] = $reason;

        $payment->update([
            'status' => 'refunded',
            'meta' => $meta,
        ]);

        return $payment->refresh();
    }

    public function detectFraud(Payment $payment): array
    {
        $riskScore = 0;

        if ($payment->status === 'failed') {
            $riskScore += 30;
        }

        if (empty($payment->external_transaction_id)) {
            $riskScore += 20;
        }

        if ((float) $payment->amount > 10000) {
            $riskScore += 50;
        }

        return [
            'payment_id' => $payment->id,
            'risk_score' => $riskScore,
            'is_suspicious' => $riskScore >= 50,
        ];
    }
}
