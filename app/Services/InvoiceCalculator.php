<?php

namespace App\Services;

use App\Models\Estimate;
use App\Models\Invoice;
use App\Models\TaxRate;

class InvoiceCalculator
{
    public function recalculate(Invoice $invoice): void
    {
        $items = $invoice->items()->get();
        $subtotal = 0;
        $taxTotal = 0;

        foreach ($items as $item) {
            $subtotal += (float) $item->line_total;
            if ($item->tax_rate_id) {
                $rate = TaxRate::find($item->tax_rate_id);
                if ($rate) {
                    $taxTotal += (float) $item->line_total * ((float) $rate->percentage / 100);
                }
            }
        }

        $discountTotal = (float) $invoice->discount_total;
        $total = $subtotal - $discountTotal + $taxTotal;
        $paid = (float) ($invoice->payments()->sum('amount') ?? 0);

        $previousStatus = $invoice->status;
        $status = $previousStatus;
        if ($status !== 'void' && $status !== 'draft') {
            if ($paid >= $total && $total > 0) {
                $status = 'paid';
            } elseif ($paid > 0) {
                $status = 'partial';
            } elseif ($invoice->due_date && $invoice->due_date->isPast()) {
                $status = 'overdue';
            }
        }

        $invoice->updateQuietly([
            'subtotal' => round($subtotal, 2),
            'tax_total' => round($taxTotal, 2),
            'total' => round($total, 2),
            'paid_total' => round($paid, 2),
            'balance_due' => round($total - $paid, 2),
            'status' => $status,
        ]);

        if ($previousStatus !== 'paid' && $status === 'paid') {
            \App\Events\InvoicePaid::dispatch($invoice->fresh(), $invoice->payments()->latest('paid_at')->first());
        }

        if ($previousStatus !== 'overdue' && $status === 'overdue') {
            \App\Events\InvoiceOverdue::dispatch($invoice->fresh());
        }
    }

    public function recalculateEstimate(Estimate $estimate): void
    {
        $items = $estimate->items()->get();
        $subtotal = 0;
        $taxTotal = 0;

        foreach ($items as $item) {
            $subtotal += (float) $item->line_total;
            if ($item->tax_rate_id) {
                $rate = TaxRate::find($item->tax_rate_id);
                if ($rate) {
                    $taxTotal += (float) $item->line_total * ((float) $rate->percentage / 100);
                }
            }
        }

        $discountTotal = (float) $estimate->discount_total;
        $total = $subtotal - $discountTotal + $taxTotal;

        $estimate->updateQuietly([
            'subtotal' => round($subtotal, 2),
            'tax_total' => round($taxTotal, 2),
            'total' => round($total, 2),
        ]);
    }
}
