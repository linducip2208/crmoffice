<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Services\InvoiceCalculator;

class LateFeeService
{
    protected InvoiceCalculator $calculator;

    public function __construct(InvoiceCalculator $calculator)
    {
        $this->calculator = $calculator;
    }

    public function applyLateFees(): void
    {
        $overdueInvoices = Invoice::query()
            ->whereIn('status', ['sent', 'partial', 'overdue'])
            ->where('balance_due', '>', 0)
            ->where('due_date', '<', now())
            ->whereNull('late_fee_charged_at')
            ->where(function ($q) {
                $q->whereNotNull('late_fee_percent')
                    ->orWhereNotNull('late_fee_fixed');
            })
            ->get();

        $count = 0;
        foreach ($overdueInvoices as $invoice) {
            $fee = $this->calculateLateFee($invoice);

            if ($fee > 0) {
                InvoiceItem::create([
                    'invoice_id' => $invoice->id,
                    'description' => 'Late Fee — ' . now()->format('d M Y'),
                    'quantity' => 1,
                    'unit_price' => $fee,
                    'line_total' => $fee,
                    'order' => ($invoice->items()->max('order') ?? 0) + 1,
                ]);

                $invoice->updateQuietly([
                    'late_fee_charged_at' => now(),
                ]);

                $this->calculator->recalculate($invoice);
                $count++;
            }
        }

        if ($count > 0) {
            \Log::info("LateFeeService: Applied late fees to {$count} overdue invoices.");
        }
    }

    protected function calculateLateFee(Invoice $invoice): float
    {
        if ($invoice->late_fee_fixed && (float) $invoice->late_fee_fixed > 0) {
            return round((float) $invoice->late_fee_fixed, 2);
        }

        if ($invoice->late_fee_percent && (float) $invoice->late_fee_percent > 0) {
            return round((float) $invoice->subtotal * ((float) $invoice->late_fee_percent / 100), 2);
        }

        return 0;
    }
}
