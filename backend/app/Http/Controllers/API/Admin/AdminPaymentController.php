<?php

namespace App\Http\Controllers\API\Admin;

use App\Http\Controllers\API\Admin\Concerns\RespondsWithApi;
use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Services\Admin\AdminPaymentManagementService;
use Illuminate\Http\Request;

class AdminPaymentController extends Controller
{
    use RespondsWithApi;

    public function __construct(private readonly AdminPaymentManagementService $service)
    {
    }

    public function index(Request $request)
    {
        return $this->success('Action completed successfully', $this->service->list($request->validate([
            'status' => ['nullable', 'string'],
            'gateway' => ['nullable', 'string'],
            'per_page' => ['nullable', 'integer', 'min:10', 'max:100'],
        ])));
    }

    public function verify(Payment $payment)
    {
        return $this->success('Action completed successfully', $this->service->verify($payment));
    }

    public function refund(Request $request, Payment $payment)
    {
        $payload = $request->validate([
            'reason' => ['required', 'string', 'max:255'],
        ]);

        return $this->success('Action completed successfully', $this->service->refund($payment, $payload['reason']));
    }

    public function fraudCheck(Payment $payment)
    {
        return $this->success('Action completed successfully', $this->service->detectFraud($payment));
    }
}
