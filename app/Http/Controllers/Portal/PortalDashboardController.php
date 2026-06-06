<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class PortalDashboardController extends Controller
{
    public function index(): View
    {
        $contact = Auth::guard('portal')->user();
        $client = $contact->client;

        $invoices = $client->invoices()->latest()->limit(5)->get([
            'id', 'number', 'status', 'total', 'currency_code', 'invoice_date', 'due_date',
        ]);

        $projects = $client->projects()
            ->where('is_visible_to_customer', true)
            ->latest()
            ->limit(5)
            ->get(['id', 'name', 'status', 'progress_pct as progress', 'deadline', 'description']);

        $tickets = $client->tickets()->with(['status', 'priority'])->latest()->limit(5)->get([
            'id', 'number', 'subject', 'status_id', 'priority_id', 'created_at',
        ]);

        return view('portal.dashboard', [
            'contact' => $contact,
            'client' => $client,
            'invoices' => $invoices,
            'projects' => $projects,
            'tickets' => $tickets,
        ]);
    }
}
