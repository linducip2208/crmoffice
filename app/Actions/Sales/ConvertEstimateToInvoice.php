<?php

namespace App\Actions\Sales;

use App\Models\Estimate;
use App\Models\Invoice;
use App\Services\NumberSequence;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ConvertEstimateToInvoice
{
    public function handle(Estimate $estimate): Invoice
    {
        return DB::transaction(function () use ($estimate) {
            if ($estimate->converted_invoice_id) {
                return $estimate->convertedInvoice;
            }

            $dueDays = (int) (\App\Models\Setting::get('invoice_due_days') ?: 14);

            $invoice = Invoice::create([
                'number' => NumberSequence::next('invoice'),
                'client_id' => $estimate->client_id,
                'estimate_id' => $estimate->id,
                'invoice_date' => now()->toDateString(),
                'due_date' => now()->addDays($dueDays)->toDateString(),
                'currency_id' => $estimate->currency_id,
                'subtotal' => 0,
                'discount_total' => $estimate->discount_total,
                'tax_total' => 0,
                'total' => 0,
                'paid_total' => 0,
                'balance_due' => 0,
                'status' => 'draft',
                'notes' => $estimate->notes,
                'terms' => $estimate->terms,
                'public_token' => Str::random(40),
                'created_by' => auth()->id(),
            ]);

            foreach ($estimate->items as $item) {
                $invoice->items()->create([
                    'item_id' => $item->item_id,
                    'description' => $item->description,
                    'quantity' => $item->quantity,
                    'unit_price' => $item->unit_price,
                    'tax_rate_id' => $item->tax_rate_id,
                    'discount_pct' => $item->discount_pct,
                    'line_total' => $item->line_total,
                    'order' => $item->order,
                ]);
            }

            $estimate->update([
                'converted_invoice_id' => $invoice->id,
                'status' => $estimate->status === 'draft' ? $estimate->status : 'converted',
            ]);

            return $invoice->fresh();
        });
    }
}
