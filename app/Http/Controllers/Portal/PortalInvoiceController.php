<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class PortalInvoiceController extends Controller
{
    public function index(): View
    {
        $contact = Auth::guard('portal')->user();
        $client = $contact->client;
        $invoices = $client->invoices()->latest()->paginate(15);

        return view('portal.invoices.index', [
            'client' => $client,
            'invoices' => $invoices,
        ]);
    }

    public function show($id): View
    {
        $contact = Auth::guard('portal')->user();
        $client = $contact->client;
        $invoice = $client->invoices()->with('items')->findOrFail($id);

        return view('portal.invoices.show', [
            'client' => $client,
            'invoice' => $invoice,
        ]);
    }
}
