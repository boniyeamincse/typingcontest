<?php

namespace App\Http\Controllers\API\Admin;

use App\Http\Controllers\API\Admin\Concerns\RespondsWithApi;
use App\Http\Controllers\Controller;
use App\Services\Admin\AdminNotificationService;
use Illuminate\Http\Request;

class AdminNotificationController extends Controller
{
    use RespondsWithApi;

    public function __construct(private readonly AdminNotificationService $service)
    {
    }

    public function send(Request $request)
    {
        $payload = $request->validate([
            'title' => ['required', 'string', 'max:160'],
            'message' => ['required', 'string'],
            'channels' => ['nullable', 'array'],
            'audience' => ['nullable', 'array'],
        ]);

        return $this->success('Action completed successfully', $this->service->send($payload, (int) $request->user()->id));
    }
}
