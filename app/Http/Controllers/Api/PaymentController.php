<?php

namespace App\Http\Controllers\Api;

use App\Actions\Sales\ApplyPaymentToInvoice;
use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\Payment;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class PaymentController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $payments = QueryBuilder::for(Payment::class)
            ->allowedFilters([
                AllowedFilter::exact('invoice_id'),
                AllowedFilter::exact('method'),
                AllowedFilter::exact('status'),
            ])
            ->allowedSorts(['paid_at', 'amount', 'created_at'])
            ->paginate(min((int) $request->query('per_page', 20), 100));

        return response()->json($payments);
    }

    public function store(Request $request, ApplyPaymentToInvoice $action): JsonResponse
    {
        $data = $request->validate([
            'invoice_id' => 'required|exists:invoices,id',
            'amount' => 'required|numeric|min:0.01',
            'currency_id' => 'required|exists:currencies,id',
            'paid_at' => 'required|date',
            'method' => 'required|string|max:40',
            'reference' => 'nullable|string|max:120',
        ]);

        $invoice = Invoice::findOrFail($data['invoice_id']);
        $payment = $action->handle($invoice, $data);

        return response()->json(['data' => $payment], 201);
    }
}
