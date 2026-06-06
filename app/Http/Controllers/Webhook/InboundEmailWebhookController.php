<?php

namespace App\Http\Controllers\Webhook;

use App\Http\Controllers\Controller;
use App\Models\Contact;
use App\Models\Department;
use App\Models\Ticket;
use App\Models\TicketPriority;
use App\Models\TicketReply;
use App\Models\TicketStatus;
use App\Services\NumberSequence;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Inbound email pipe webhook.
 *
 * Provider posts a normalized payload after parsing the email (Mailgun routes,
 * Postmark inbound, SendGrid parse, etc.). Token in URL identifies the
 * department/inbound mailbox configured by admin.
 */
class InboundEmailWebhookController extends Controller
{
    public function handle(Request $request, string $token, NumberSequence $sequence): JsonResponse
    {
        $department = Department::query()
            ->where('inbound_token', $token)
            ->where('is_active', true)
            ->first();

        if (! $department) {
            return response()->json(['error' => 'Unknown mailbox'], 404);
        }

        $data = $request->validate([
            'from' => 'required|email',
            'subject' => 'required|string|max:500',
            'body' => 'required|string',
            'message_id' => 'nullable|string|max:255',
            'in_reply_to' => 'nullable|string|max:255',
        ]);

        // Match reply to existing ticket via [#NUMBER] tag in subject
        $ticket = null;
        if (preg_match('/\[#([A-Z0-9-]+)\]/i', $data['subject'], $m)) {
            $ticket = Ticket::where('number', $m[1])->first();
        }

        $contact = Contact::where('email', $data['from'])->first();

        if ($ticket) {
            TicketReply::create([
                'ticket_id' => $ticket->id,
                'contact_id' => $contact?->id,
                'email_from' => $data['from'],
                'body' => $data['body'],
                'is_internal' => false,
                'message_id' => $data['message_id'] ?? null,
            ]);

            return response()->json(['ok' => true, 'matched' => 'reply', 'ticket' => $ticket->number]);
        }

        $priority = TicketPriority::orderBy('sort_order')->firstOrFail();
        $status = TicketStatus::orderBy('sort_order')->firstOrFail();

        $ticket = Ticket::create([
            'number' => $sequence->next('ticket'),
            'subject' => $data['subject'],
            'body' => $data['body'],
            'client_id' => $contact?->client_id,
            'contact_id' => $contact?->id,
            'email_from' => $data['from'],
            'department_id' => $department->id,
            'priority_id' => $priority->id,
            'status_id' => $status->id,
        ]);

        return response()->json(['ok' => true, 'matched' => 'new', 'ticket' => $ticket->number], 201);
    }
}
