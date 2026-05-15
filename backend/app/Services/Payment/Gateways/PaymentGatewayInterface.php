<?php

namespace App\Services\Payment\Gateways;

interface PaymentGatewayInterface
{
    public function createPayment(array $payload): array;

    public function verifyPayment(array $payload): array;

    public function verifyWebhookSignature(array $payload, ?string $signature): bool;
}
