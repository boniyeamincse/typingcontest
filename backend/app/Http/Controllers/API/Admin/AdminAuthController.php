<?php

namespace App\Http\Controllers\API\Admin;

use App\Http\Controllers\API\Admin\Concerns\RespondsWithApi;
use App\Http\Controllers\Controller;
use App\Services\Admin\AdminAuthService;
use Illuminate\Http\Request;

class AdminAuthController extends Controller
{
    use RespondsWithApi;

    public function __construct(private readonly AdminAuthService $service)
    {
    }

    public function login(Request $request)
    {
        $payload = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
            'device_name' => ['nullable', 'string', 'max:120'],
        ]);

        $result = $this->service->login($payload['email'], $payload['password'], $payload['device_name'] ?? null);

        return $this->success('Action completed successfully', $result);
    }

    public function logout(Request $request)
    {
        $this->service->logout($request->user());

        return $this->success('Action completed successfully', []);
    }
}
