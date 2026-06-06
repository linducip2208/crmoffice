<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Services\InvoiceCalculator;
use App\Services\NumberSequence;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class InvoiceController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $invoices = QueryBuilder::for(Invoice::class)
            ->allowedFilters([
                AllowedFilter::exact('client_id'),
                AllowedFilter::exact('status'),
                AllowedFilter::exact('project_id'),
                AllowedFilter::scope('overdue'),
            ])
            ->allowedSorts(['invoice_date', 'due_date', 'total', 'created_at'])
            ->allowedIncludes(['client', 'items', 'payments'])
            ->paginate(min((int) $request->query('per_page', 20), 100));

        return response()->json($invoices);
    }

    public function show(Invoice $invoice): JsonResponse
    {
        $invoice->load(['client', 'items.taxRate', 'payments']);

        return response()->json(['data' => $invoice]);
    }

    public function store(Request $request, NumberSequence $sequence, InvoiceCalculator $calc): JsonResponse
    {
        $data = $request->validate([
            'client_id' => 'required|exists:clients,id',
            'project_id' => 'nullable|exists:projects,id',
            'invoice_date' => 'required|date',
            'due_date' => 'required|date',
            'currency_id' => 'required|exists:currencies,id',
            'items' => 'required|array|min:1',
            'items.*.description' => 'required|string',
            'items.*.quantity' => 'required|numeric|min:0',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.tax_rate_id' => 'nullable|exists:tax_rates,id',
            'notes' => 'nullable|string',
            'terms' => 'nullable|string',
        ]);

        $invoice = Invoice::create([
            'number' => $sequence->next('invoice'),
            'client_id' => $data['client_id'],
            'project_id' => $data['project_id'] ?? null,
            'invoice_date' => $data['invoice_date'],
            'due_date' => $data['due_date'],
            'currency_id' => $data['currency_id'],
            'status' => 'draft',
            'public_token' => Str::random(40),
            'notes' => $data['notes'] ?? null,
            'terms' => $data['terms'] ?? null,
            'created_by' => $request->user()->id,
        ]);

        foreach ($data['items'] as $item) {
            $invoice->items()->create([
                'description' => $item['description'],
                'quantity' => $item['quantity'],
                'unit_price' => $item['unit_price'],
                'line_total' => $item['quantity'] * $item['unit_price'],
                'tax_rate_id' => $item['tax_rate_id'] ?? null,
            ]);
        }

        $calc->recalculate($invoice);

        return response()->json(['data' => $invoice->fresh(['items'])], 201);
    }

    public function destroy(Invoice $invoice): JsonResponse
    {
        $invoice->delete();

        return response()->json(null, 204);
    }
}
