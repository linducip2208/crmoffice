<?php

namespace App\Http\Controllers\Api\Portal;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PortalInvoiceController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $contact = $request->user();

        $invoices = Invoice::query()
            ->where('client_id', $contact->client_id)
            ->orderByDesc('invoice_date')
            ->paginate(20);

        return response()->json($invoices);
    }

    public function show(Request $request, Invoice $invoice): JsonResponse
    {
        $this->authorizeOwnership($request, $invoice);

        $invoice->load(['items.taxRate', 'payments', 'currency']);

        return response()->json(['data' => $invoice]);
    }

    private function authorizeOwnership(Request $request, Invoice $invoice): void
    {
        if ($invoice->client_id !== $request->user()->client_id) {
            abort(404);
        }
    }
}
