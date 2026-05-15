<?php

namespace App\Http\Controllers\API\Payment;

use App\Http\Controllers\Controller;
use App\Services\Payment\PaymentAdminService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminPaymentController extends Controller
{
    public function __construct(private readonly PaymentAdminService $paymentAdminService) {}

    public function refund(Request $request, string $paymentIntentId): JsonResponse
    {
        $payload = $request->validate([
            'reason' => ['nullable', 'string', 'max:255'],
        ]);

        $data = $this->paymentAdminService->refundByIntent($paymentIntentId, $payload['reason'] ?? null);

        return response()->json([
            'success' => true,
            'message' => 'Refund processed successfully',
            'data' => $data,
        ]);
    }
}
