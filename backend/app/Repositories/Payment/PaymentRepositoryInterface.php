<?php

namespace App\Repositories\Payment;

use App\Models\Invoice;
use App\Models\Payment;
use App\Models\User;
use App\Models\UserSubscription;
use Illuminate\Support\Collection;

interface PaymentRepositoryInterface
{
    public function createPayment(User $user, UserSubscription $subscription, array $attributes): Payment;

    public function findByIntentId(string $intentId): ?Payment;

    public function findPaidByExternalTransaction(string $gateway, string $externalTransactionId): ?Payment;

    public function markPaid(Payment $payment, array $payload): Payment;

    public function markFailed(Payment $payment, string $reason, array $payload = []): Payment;

    public function addTransaction(Payment $payment, array $attributes): void;

    public function createInvoice(Payment $payment): Invoice;

    public function getHistory(User $user, int $limit = 20): Collection;

    public function countCouponUsageForUser(int $couponCodeId, int $userId): int;

    public function incrementCouponUsage(int $couponCodeId): void;
}
