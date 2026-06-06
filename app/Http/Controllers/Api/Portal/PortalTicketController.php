<?php

namespace App\Http\Controllers\Api\Portal;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\Ticket;
use App\Models\TicketPriority;
use App\Models\TicketReply;
use App\Models\TicketStatus;
use App\Services\NumberSequence;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PortalTicketController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $tickets = Ticket::query()
            ->where('client_id', $request->user()->client_id)
            ->orderByDesc('updated_at')
            ->paginate(20);

        return response()->json($tickets);
    }

    public function show(Request $request, Ticket $ticket): JsonResponse
    {
        if ($ticket->client_id !== $request->user()->client_id) {
            abort(404);
        }

        $ticket->load([
            'replies' => fn ($q) => $q->where('is_internal', false)->orderBy('created_at'),
            'status', 'priority', 'department',
        ]);

        return response()->json(['data' => $ticket]);
    }

    public function store(Request $request, NumberSequence $sequence): JsonResponse
    {
        $data = $request->validate([
            'subject' => 'required|string|max:255',
            'body' => 'required|string',
            'department_id' => 'nullable|exists:departments,id',
            'priority_id' => 'nullable|exists:ticket_priorities,id',
        ]);

        $contact = $request->user();

        $ticket = Ticket::create([
            'number' => $sequence->next('ticket'),
            'subject' => $data['subject'],
            'body' => $data['body'],
            'client_id' => $contact->client_id,
            'contact_id' => $contact->id,
            'email_from' => $contact->email,
            'department_id' => $data['department_id']
                ?? Department::where('is_active', true)->orderBy('id')->value('id'),
            'priority_id' => $data['priority_id']
                ?? TicketPriority::orderBy('sort_order')->value('id'),
            'status_id' => TicketStatus::orderBy('sort_order')->value('id'),
        ]);

        return response()->json(['data' => $ticket], 201);
    }

    public function reply(Request $request, Ticket $ticket): JsonResponse
    {
        if ($ticket->client_id !== $request->user()->client_id) {
            abort(404);
        }

        $data = $request->validate([
            'body' => 'required|string',
        ]);

        $reply = TicketReply::create([
            'ticket_id' => $ticket->id,
            'contact_id' => $request->user()->id,
            'email_from' => $request->user()->email,
            'body' => $data['body'],
            'is_internal' => false,
        ]);

        return response()->json(['data' => $reply], 201);
    }
}
