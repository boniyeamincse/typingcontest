<?php

namespace App\Http\Controllers\API\Payment;

use App\Http\Controllers\Controller;
use App\Services\Payment\InvoicePdfService;
use App\Services\Payment\PaymentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    public function __construct(
        private readonly PaymentService $paymentService,
        private readonly InvoicePdfService $invoicePdfService,
    ) {}

    public function create(Request $request): JsonResponse
    {
        $payload = $request->validate([
            'payment_intent_id' => ['required', 'string', 'max:64'],
        ]);

        $data = $this->paymentService->create($request->user(), $payload);

        return $this->success('Payment request created successfully', $data);
    }

    public function verify(Request $request): JsonResponse
    {
        $payload = $request->validate([
            'payment_intent_id' => ['required', 'string', 'max:64'],
            'status' => ['required', 'string'],
            'amount' => ['required', 'numeric', 'min:0'],
            'gateway_reference' => ['nullable', 'string', 'max:128'],
            'external_transaction_id' => ['nullable', 'string', 'max:128'],
        ]);

        $data = $this->paymentService->verify($request->user(), $payload);

        return $this->success('Payment verified successfully', $data);
    }

    public function webhook(Request $request): JsonResponse
    {
        $payload = $request->validate([
            'gateway' => ['required', 'in:bkash,nagad,sslcommerz'],
            'payment_intent_id' => ['required', 'string', 'max:64'],
            'status' => ['required', 'string'],
            'amount' => ['required', 'numeric', 'min:0'],
            'gateway_reference' => ['nullable', 'string', 'max:128'],
            'external_transaction_id' => ['nullable', 'string', 'max:128'],
        ]);

        $signature = (string) $request->header('X-Payment-Signature', '');
        $this->paymentService->queueWebhook($payload['gateway'], $payload, $signature !== '' ? $signature : null);

        return $this->success('Webhook accepted for processing', ['queued' => true]);
    }

    public function history(Request $request): JsonResponse
    {
        $limit = max(1, min((int) $request->query('limit', 20), 100));
        $rows = $this->paymentService->history($request->user(), $limit)->map(function ($payment): array {
            return [
                'payment_intent_id' => $payment->payment_intent_id,
                'gateway' => $payment->gateway,
                'status' => $payment->status,
                'amount' => (float) $payment->amount,
                'discount_amount' => (float) $payment->discount_amount,
                'final_amount' => (float) $payment->final_amount,
                'currency' => $payment->currency,
                'paid_at' => optional($payment->paid_at)->toISOString(),
                'plan' => [
                    'code' => $payment->plan?->code,
                    'name' => $payment->plan?->name,
                    'tier' => $payment->plan?->tier,
                ],
                'transactions' => $payment->transactions->map(fn ($txn) => [
                    'type' => $txn->transaction_type,
                    'status' => $txn->status,
                    'processed_at' => optional($txn->processed_at)->toISOString(),
                ])->values(),
            ];
        })->values();

        return $this->success('Payment history fetched successfully', $rows);
    }

    public function downloadInvoice(Request $request, string $paymentIntentId)
    {
        $invoice = $this->paymentService->getInvoiceByPaymentIntent($request->user(), $paymentIntentId);

        if (!$invoice) {
            return response()->json([
                'success' => false,
                'message' => 'Invoice not found',
            ], 404);
        }

        $pdf = $this->invoicePdfService->generate($invoice);

        return response($pdf, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="' . $invoice->invoice_number . '.pdf"',
        ]);
    }

    private function success(string $message, mixed $data): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $data,
        ]);
    }
}
