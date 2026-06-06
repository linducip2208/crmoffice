<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Ticket;
use App\Services\NumberSequence;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class TicketController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $tickets = QueryBuilder::for(Ticket::class)
            ->allowedFilters([
                AllowedFilter::exact('status_id'),
                AllowedFilter::exact('priority_id'),
                AllowedFilter::exact('department_id'),
                AllowedFilter::exact('assigned_to'),
                AllowedFilter::partial('subject'),
            ])
            ->allowedSorts(['created_at', 'first_response_due_at', 'resolve_due_at'])
            ->allowedIncludes(['status', 'priority', 'department', 'client', 'replies'])
            ->paginate(min((int) $request->query('per_page', 20), 100));

        return response()->json($tickets);
    }

    public function show(Ticket $ticket): JsonResponse
    {
        $ticket->load(['status', 'priority', 'department', 'client', 'replies']);

        return response()->json(['data' => $ticket]);
    }

    public function store(Request $request, NumberSequence $sequence): JsonResponse
    {
        $data = $request->validate([
            'subject' => 'required|string|max:255',
            'body' => 'required|string',
            'client_id' => 'nullable|exists:clients,id',
            'contact_id' => 'nullable|exists:contacts,id',
            'department_id' => 'required|exists:departments,id',
            'priority_id' => 'required|exists:ticket_priorities,id',
            'status_id' => 'required|exists:ticket_statuses,id',
            'assigned_to' => 'nullable|exists:users,id',
        ]);

        $ticket = Ticket::create($data + ['number' => $sequence->next('ticket')]);

        return response()->json(['data' => $ticket], 201);
    }

    public function update(Request $request, Ticket $ticket): JsonResponse
    {
        $data = $request->validate([
            'subject' => 'sometimes|required|string|max:255',
            'status_id' => 'sometimes|exists:ticket_statuses,id',
            'priority_id' => 'sometimes|exists:ticket_priorities,id',
            'assigned_to' => 'nullable|exists:users,id',
        ]);

        $ticket->update($data);

        return response()->json(['data' => $ticket]);
    }
}
