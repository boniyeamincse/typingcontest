<?php

namespace App\Services\Payment;

use App\Services\Payment\Gateways\BkashGatewayService;
use App\Services\Payment\Gateways\NagadGatewayService;
use App\Services\Payment\Gateways\PaymentGatewayInterface;
use App\Services\Payment\Gateways\SslCommerzGatewayService;
use InvalidArgumentException;

class PaymentGatewayResolver
{
    public function resolve(string $gateway): PaymentGatewayInterface
    {
        return match (strtolower($gateway)) {
            'bkash' => app(BkashGatewayService::class),
            'nagad' => app(NagadGatewayService::class),
            'sslcommerz' => app(SslCommerzGatewayService::class),
            default => throw new InvalidArgumentException('Unsupported payment gateway'),
        };
    }
}
