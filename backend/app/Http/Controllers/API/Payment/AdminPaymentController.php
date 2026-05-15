<?php

namespace App\Http\Controllers\API\Payment;

use App\Http\Controllers\Controller;
use App\Services\Payment\PaymentAdminService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminPaymentController extends Controller
{
    public function __construct(private readonly PaymentAdminService $paymentAdminService) {}

    public function export(Request $request)
    {
        $filters = $request->validate([
            'from_date' => ['nullable', 'date'],
            'to_date' => ['nullable', 'date', 'after_or_equal:from_date'],
            'gateway' => ['nullable', 'in:bkash,nagad,sslcommerz'],
            'payment_status' => ['nullable', 'in:pending,processing,paid,failed,refunded'],
            'subscription_status' => ['nullable', 'in:pending,active,cancelled,expired,grace'],
            'coupon_code' => ['nullable', 'string', 'max:64'],
            'refund_reason' => ['nullable', 'string', 'max:255'],
            'plan_code' => ['nullable', 'string', 'max:32'],
            'user_id' => ['nullable', 'integer', 'exists:users,id'],
            'limit' => ['nullable', 'integer', 'min:1', 'max:5000'],
        ]);

        $csv = $this->paymentAdminService->exportCsv($filters);

        return response($csv['content'], 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $csv['filename'] . '"',
        ]);
    }

    public function report(Request $request): JsonResponse
    {
        $filters = $request->validate([
            'from_date' => ['nullable', 'date'],
            'to_date' => ['nullable', 'date', 'after_or_equal:from_date'],
            'gateway' => ['nullable', 'in:bkash,nagad,sslcommerz'],
            'payment_status' => ['nullable', 'in:pending,processing,paid,failed,refunded'],
            'subscription_status' => ['nullable', 'in:pending,active,cancelled,expired,grace'],
            'coupon_code' => ['nullable', 'string', 'max:64'],
            'refund_reason' => ['nullable', 'string', 'max:255'],
            'plan_code' => ['nullable', 'string', 'max:32'],
            'user_id' => ['nullable', 'integer', 'exists:users,id'],
            'limit' => ['nullable', 'integer', 'min:1', 'max:200'],
        ]);

        $data = $this->paymentAdminService->report($filters);

        return response()->json([
            'success' => true,
            'message' => 'Admin report fetched successfully',
            'data' => $data,
        ]);
    }

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
