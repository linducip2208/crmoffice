<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\Ticket;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class PortalTicketController extends Controller
{
    public function index(): View
    {
        $contact = Auth::guard('portal')->user();
        $client = $contact->client;
        $tickets = $client->tickets()->with(['status', 'priority'])->latest()->paginate(15);

        return view('portal.tickets.index', [
            'client' => $client,
            'tickets' => $tickets,
        ]);
    }

    public function show($id): View
    {
        $contact = Auth::guard('portal')->user();
        $client = $contact->client;
        $ticket = $client->tickets()->with(['replies', 'status', 'priority', 'department'])->findOrFail($id);

        return view('portal.tickets.show', [
            'client' => $client,
            'ticket' => $ticket,
        ]);
    }

    public function create(): View
    {
        $contact = Auth::guard('portal')->user();
        $client = $contact->client;

        return view('portal.tickets.create', [
            'client' => $client,
            'departments' => \App\Models\Department::all(),
            'priorities' => \App\Models\TicketPriority::all(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $contact = Auth::guard('portal')->user();
        $client = $contact->client;

        $data = $request->validate([
            'subject' => 'required|string|max:255',
            'body' => 'required|string',
            'department_id' => 'required|exists:departments,id',
            'priority_id' => 'required|exists:ticket_priorities,id',
        ]);

        $lastNumber = Ticket::max('id') ?? 0;
        $number = 'T-' . str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);

        $openStatus = \App\Models\TicketStatus::where('is_open', true)->first();

        $ticket = Ticket::create([
            'number' => $number,
            'subject' => $data['subject'],
            'body' => $data['body'],
            'client_id' => $client->id,
            'contact_id' => $contact->id,
            'department_id' => $data['department_id'],
            'priority_id' => $data['priority_id'],
            'status_id' => $openStatus?->id,
        ]);

        return redirect()->route('portal.tickets.show', $ticket->id)
            ->with('success', 'Ticket berhasil dibuat.');
    }
}
