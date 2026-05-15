<?php

namespace App\Http\Controllers\API\Admin;

use App\Http\Controllers\API\Admin\Concerns\RespondsWithApi;
use App\Http\Controllers\Controller;
use App\Services\Admin\AdminLeaderboardControlService;
use Illuminate\Http\Request;

class AdminLeaderboardController extends Controller
{
    use RespondsWithApi;

    public function __construct(private readonly AdminLeaderboardControlService $service)
    {
    }

    public function reset(Request $request)
    {
        $payload = $request->validate(['period' => ['nullable', 'string']]);

        return $this->success('Action completed successfully', ['deleted' => $this->service->reset($payload['period'] ?? null)]);
    }

    public function recalculate(Request $request)
    {
        $payload = $request->validate(['period' => ['nullable', 'string']]);

        return $this->success('Action completed successfully', ['updated' => $this->service->recalculate($payload['period'] ?? null)]);
    }

    public function removeFake(Request $request)
    {
        $payload = $request->validate(['result_ids' => ['required', 'array', 'min:1']]);

        return $this->success('Action completed successfully', ['removed' => $this->service->removeFakeScores($payload['result_ids'])]);
    }

    public function pinTop(Request $request)
    {
        $payload = $request->validate(['user_ids' => ['required', 'array', 'min:1']]);

        return $this->success('Action completed successfully', ['updated' => $this->service->pinTopUsers($payload['user_ids'])]);
    }

    public function export()
    {
        return $this->success('Action completed successfully', $this->service->export());
    }
}
