<?php

namespace App\Jobs\Payment;

use App\Services\Payment\PaymentService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessPaymentWebhookJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 5;
    public array $backoff = [10, 30, 60, 120];
    public string $queue = 'payments';

    public function __construct(
        private readonly string $gateway,
        private readonly array $payload,
        private readonly ?string $signature,
    ) {}

    public function handle(PaymentService $paymentService): void
    {
        $paymentService->handleWebhook($this->gateway, $this->payload, $this->signature);
    }
}
