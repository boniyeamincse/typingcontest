<?php

namespace App\Services\Admin;

use App\Models\SupportTicket;
use App\Models\SupportTicketMessage;
use Illuminate\Support\Str;

class AdminSupportService
{
    public function list(array $filters)
    {
        return SupportTicket::query()
            ->with(['user:id,name,email', 'assignedAdmin:id,name'])
            ->when($filters['status'] ?? null, fn ($q, $status) => $q->where('status', $status))
            ->latest('id')
            ->paginate(max(10, min(100, (int) ($filters['per_page'] ?? 20))));
    }

    public function create(array $payload): SupportTicket
    {
        $payload['ticket_no'] = 'TKT-'.strtoupper(Str::random(8));

        return SupportTicket::create($payload);
    }

    public function respond(SupportTicket $ticket, int $senderId, string $message, array $attachments = []): SupportTicketMessage
    {
        return SupportTicketMessage::create([
            'support_ticket_id' => $ticket->id,
            'sender_id' => $senderId,
            'message' => $message,
            'attachments' => $attachments,
        ]);
    }

    public function close(SupportTicket $ticket): SupportTicket
    {
        $ticket->update(['status' => 'closed']);

        return $ticket->refresh();
    }

    public function escalate(SupportTicket $ticket): SupportTicket
    {
        $ticket->update(['status' => 'escalated']);

        return $ticket->refresh();
    }
}
