<?php

namespace App\Services\Payment\Gateways;

class BkashGatewayService implements PaymentGatewayInterface
{
    public function createPayment(array $payload): array
    {
        return [
            'gateway_reference' => 'BKASH-' . strtoupper(substr(hash('sha256', ($payload['payment_intent_id'] ?? '') . now()->timestamp), 0, 16)),
            'redirect_url' => rtrim((string) env('BKASH_BASE_URL', 'https://sandbox.payment.bkash.com'), '/') . '/checkout?intent=' . urlencode((string) ($payload['payment_intent_id'] ?? '')),
            'status' => 'pending',
        ];
    }

    public function verifyPayment(array $payload): array
    {
        return [
            'verified' => (($payload['status'] ?? 'success') === 'success'),
            'gateway_reference' => $payload['gateway_reference'] ?? null,
            'external_transaction_id' => $payload['external_transaction_id'] ?? null,
            'amount' => (float) ($payload['amount'] ?? 0),
            'message' => 'bKash payment verification processed',
        ];
    }

    public function verifyWebhookSignature(array $payload, ?string $signature): bool
    {
        if (!$signature) {
            return false;
        }

        $secret = (string) env('BKASH_WEBHOOK_SECRET', 'bkash-secret');
        $raw = json_encode($payload, JSON_UNESCAPED_SLASHES);
        $expected = hash_hmac('sha256', $raw ?: '', $secret);

        return hash_equals($expected, $signature);
    }
}
