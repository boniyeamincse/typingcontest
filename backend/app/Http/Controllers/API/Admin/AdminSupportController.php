<?php

namespace App\Http\Controllers\API\Admin;

use App\Http\Controllers\API\Admin\Concerns\RespondsWithApi;
use App\Http\Controllers\Controller;
use App\Models\SupportTicket;
use App\Services\Admin\AdminSupportService;
use Illuminate\Http\Request;

class AdminSupportController extends Controller
{
    use RespondsWithApi;

    public function __construct(private readonly AdminSupportService $service)
    {
    }

    public function index(Request $request)
    {
        return $this->success('Action completed successfully', $this->service->list($request->validate([
            'status' => ['nullable', 'string'],
            'per_page' => ['nullable', 'integer', 'min:10', 'max:100'],
        ])));
    }

    public function store(Request $request)
    {
        $payload = $request->validate([
            'user_id' => ['required', 'integer', 'exists:users,id'],
            'subject' => ['required', 'string', 'max:160'],
            'description' => ['required', 'string'],
            'priority' => ['nullable', 'string', 'in:low,medium,high,critical'],
            'assigned_admin_id' => ['nullable', 'integer', 'exists:users,id'],
            'evidence' => ['nullable', 'array'],
        ]);

        return $this->success('Action completed successfully', $this->service->create($payload), 201);
    }

    public function respond(Request $request, SupportTicket $ticket)
    {
        $payload = $request->validate([
            'message' => ['required', 'string'],
            'attachments' => ['nullable', 'array'],
        ]);

        return $this->success('Action completed successfully', $this->service->respond(
            $ticket,
            (int) $request->user()->id,
            $payload['message'],
            $payload['attachments'] ?? []
        ));
    }

    public function close(SupportTicket $ticket)
    {
        return $this->success('Action completed successfully', $this->service->close($ticket));
    }

    public function escalate(SupportTicket $ticket)
    {
        return $this->success('Action completed successfully', $this->service->escalate($ticket));
    }
}
