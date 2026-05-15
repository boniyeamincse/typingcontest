<?php

namespace App\Http\Controllers\API\Admin;

use App\Http\Controllers\API\Admin\Concerns\RespondsWithApi;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\Admin\AdminUserService;
use Illuminate\Http\Request;

class AdminUserController extends Controller
{
    use RespondsWithApi;

    public function __construct(private readonly AdminUserService $service)
    {
    }

    public function index(Request $request)
    {
        $payload = $request->validate([
            'plan' => ['nullable', 'string', 'in:free,pro,vip'],
            'search' => ['nullable', 'string', 'max:100'],
            'per_page' => ['nullable', 'integer', 'min:10', 'max:100'],
        ]);

        return $this->success('Action completed successfully', $this->service->list($payload));
    }

    public function suspicious(Request $request)
    {
        $payload = $request->validate([
            'country' => ['nullable', 'string', 'max:3'],
            'per_page' => ['nullable', 'integer', 'min:10', 'max:100'],
        ]);

        return $this->success('Action completed successfully', $this->service->suspicious($payload));
    }

    public function ban(Request $request)
    {
        $payload = $request->validate([
            'user_id' => ['required', 'integer', 'exists:users,id'],
            'reason' => ['required', 'string', 'max:255'],
        ]);

        $user = User::query()->findOrFail($payload['user_id']);

        return $this->success('Action completed successfully', $this->service->ban($request->user(), $user, $payload['reason']));
    }

    public function unban(Request $request)
    {
        $payload = $request->validate([
            'user_id' => ['required', 'integer', 'exists:users,id'],
        ]);

        $user = User::query()->findOrFail($payload['user_id']);

        return $this->success('Action completed successfully', $this->service->unban($request->user(), $user));
    }

    public function suspend(Request $request)
    {
        $payload = $request->validate([
            'user_id' => ['required', 'integer', 'exists:users,id'],
            'until' => ['required', 'date'],
            'reason' => ['required', 'string', 'max:255'],
        ]);

        $user = User::query()->findOrFail($payload['user_id']);

        return $this->success('Action completed successfully', $this->service->suspend($request->user(), $user, $payload['until'], $payload['reason']));
    }

    public function resetPassword(Request $request)
    {
        $payload = $request->validate([
            'user_id' => ['required', 'integer', 'exists:users,id'],
            'new_password' => ['required', 'string', 'min:8', 'max:120'],
        ]);

        $user = User::query()->findOrFail($payload['user_id']);
        $this->service->resetPassword($request->user(), $user, $payload['new_password']);

        return $this->success('Action completed successfully', []);
    }
}
