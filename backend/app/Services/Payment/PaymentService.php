<?php

namespace App\Services\Payment;

use App\Events\Payment\PaymentConfirmed;
use App\Jobs\Payment\ProcessPaymentWebhookJob;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\User;
use App\Repositories\Payment\PaymentRepositoryInterface;
use App\Services\Subscription\SubscriptionService;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PaymentService
{
    public function __construct(
        private readonly PaymentRepositoryInterface $paymentRepository,
        private readonly PaymentGatewayResolver $gatewayResolver,
        private readonly SubscriptionService $subscriptionService,
    ) {}

    public function create(User $user, array $payload): array
    {
        $payment = $this->paymentRepository->findByIntentId($payload['payment_intent_id']);
        if (!$payment || $payment->user_id !== $user->id) {
            throw ValidationException::withMessages(['payment_intent_id' => ['Payment intent not found.']]);
        }

        if ($payment->status !== 'pending') {
            throw ValidationException::withMessages(['payment' => ['Payment is already processed.']]);
        }

        $gateway = $this->gatewayResolver->resolve($payment->gateway);
        $gatewayPayload = $gateway->createPayment([
            'payment_intent_id' => $payment->payment_intent_id,
            'amount' => (float) $payment->final_amount,
            'currency' => $payment->currency,
        ]);

        $payment->update([
            'status' => 'processing',
            'gateway_reference' => $gatewayPayload['gateway_reference'] ?? null,
        ]);

        $this->paymentRepository->addTransaction($payment, [
            'transaction_type' => 'create',
            'status' => 'success',
            'gateway_transaction_id' => $gatewayPayload['gateway_reference'] ?? null,
            'request_payload' => ['intent' => $payment->payment_intent_id],
            'response_payload' => $gatewayPayload,
            'processed_at' => now(),
            'message' => 'Payment create request generated',
        ]);

        return [
            'payment' => $payment->fresh(),
            'gateway' => $payment->gateway,
            'redirect_url' => $gatewayPayload['redirect_url'] ?? null,
            'gateway_reference' => $gatewayPayload['gateway_reference'] ?? null,
        ];
    }

    public function verify(User $user, array $payload): array
    {
        $payment = $this->paymentRepository->findByIntentId($payload['payment_intent_id']);

        if (!$payment || $payment->user_id !== $user->id) {
            throw ValidationException::withMessages(['payment_intent_id' => ['Payment intent not found.']]);
        }

        return $this->verifyAndFinalize($payment, $payload, null, 'verify');
    }

    public function queueWebhook(string $gateway, array $payload, ?string $signature): void
    {
        ProcessPaymentWebhookJob::dispatch($gateway, $payload, $signature);
    }

    public function handleWebhook(string $gateway, array $payload, ?string $signature): array
    {
        $intentId = (string) ($payload['payment_intent_id'] ?? '');
        if ($intentId === '') {
            throw ValidationException::withMessages(['payment_intent_id' => ['Missing payment intent id']]);
        }

        $payment = $this->paymentRepository->findByIntentId($intentId);
        if (!$payment) {
            throw ValidationException::withMessages(['payment_intent_id' => ['Payment not found']]);
        }

        if ($payment->gateway !== strtolower($gateway)) {
            throw ValidationException::withMessages(['gateway' => ['Gateway mismatch for payment intent']]);
        }

        return $this->verifyAndFinalize($payment, $payload, $signature, 'webhook');
    }

    public function history(User $user, int $limit = 20)
    {
        return $this->paymentRepository->getHistory($user, $limit);
    }

    public function getInvoiceByPaymentIntent(User $user, string $paymentIntentId): ?Invoice
    {
        $payment = $this->paymentRepository->findByIntentId($paymentIntentId);
        if (!$payment || $payment->user_id !== $user->id) {
            return null;
        }

        return \App\Models\Invoice::query()->where('payment_id', $payment->id)->first();
    }

    private function verifyAndFinalize(Payment $payment, array $payload, ?string $signature, string $source): array
    {
        $gateway = $this->gatewayResolver->resolve($payment->gateway);

        if ($source === 'webhook' && !$gateway->verifyWebhookSignature($payload, $signature)) {
            $this->paymentRepository->addTransaction($payment, [
                'transaction_type' => 'webhook_verify',
                'status' => 'failed',
                'signature' => $signature,
                'request_payload' => $payload,
                'processed_at' => now(),
                'message' => 'Invalid webhook signature',
            ]);

            throw ValidationException::withMessages(['signature' => ['Invalid webhook signature']]);
        }

        $verification = $gateway->verifyPayment($payload);

        $externalTxnId = (string) ($verification['external_transaction_id'] ?? '');
        if ($externalTxnId !== '') {
            $alreadyPaid = $this->paymentRepository->findPaidByExternalTransaction($payment->gateway, $externalTxnId);
            if ($alreadyPaid && $alreadyPaid->id !== $payment->id) {
                throw ValidationException::withMessages(['transaction' => ['Duplicate transaction detected']]);
            }
        }

        $amount = (float) ($verification['amount'] ?? 0);
        $expectedAmount = (float) $payment->final_amount;

        if (!$verification['verified'] || abs($amount - $expectedAmount) > 0.01) {
            $failed = $this->paymentRepository->markFailed($payment, 'Payment verification failed or amount mismatch', $verification);
            $this->paymentRepository->addTransaction($failed, [
                'transaction_type' => $source,
                'status' => 'failed',
                'gateway_transaction_id' => $externalTxnId ?: null,
                'signature' => $signature,
                'request_payload' => $payload,
                'response_payload' => $verification,
                'processed_at' => now(),
                'message' => 'Verification failed',
            ]);

            throw ValidationException::withMessages(['payment' => ['Payment failed']]);
        }

        $paid = DB::transaction(function () use ($payment, $verification, $payload, $signature, $externalTxnId, $source): Payment {
            $paid = $this->paymentRepository->markPaid($payment, $verification);

            $this->paymentRepository->addTransaction($paid, [
                'transaction_type' => $source,
                'status' => 'success',
                'gateway_transaction_id' => $externalTxnId ?: null,
                'signature' => $signature,
                'request_payload' => $payload,
                'response_payload' => $verification,
                'processed_at' => now(),
                'message' => 'Payment confirmed',
            ]);

            $invoice = $this->paymentRepository->createInvoice($paid);

            if ($paid->coupon_code_id) {
                $this->paymentRepository->incrementCouponUsage((int) $paid->coupon_code_id);
            }

            $this->subscriptionService->syncAfterPayment($paid->user, $paid->subscription);

            $this->safeBroadcast(new PaymentConfirmed($paid));

            $paid->setRelation('invoice', $invoice);

            return $paid;
        });

        return [
            'payment' => $paid,
            'invoice' => $paid->getRelation('invoice'),
        ];
    }

    private function safeBroadcast(object $event): void
    {
        if (config('broadcasting.default') === 'redis' && !class_exists(\Redis::class)) {
            return;
        }

        try {
            event($event);
        } catch (\Throwable $exception) {
            report($exception);
        }
    }
}
