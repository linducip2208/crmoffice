<?php

namespace App\Actions\Sales;

use App\Models\Invoice;
use App\Models\Payment;
use App\Services\InvoiceCalculator;
use Illuminate\Support\Facades\DB;

class ApplyPaymentToInvoice
{
    public function handle(Invoice $invoice, array $data): Payment
    {
        return DB::transaction(function () use ($invoice, $data) {
            $payment = $invoice->payments()->create([
                'amount' => $data['amount'],
                'currency_id' => $data['currency_id'] ?? $invoice->currency_id,
                'method' => $data['method'] ?? 'manual',
                'provider_id' => $data['provider_id'] ?? null,
                'transaction_id' => $data['transaction_id'] ?? null,
                'paid_at' => $data['paid_at'] ?? now(),
                'note' => $data['note'] ?? null,
                'status' => 'completed',
                'raw_payload' => $data['raw_payload'] ?? null,
            ]);

            // Observer auto-recalculates totals
            app(InvoiceCalculator::class)->recalculate($invoice->fresh());

            return $payment;
        });
    }
}
