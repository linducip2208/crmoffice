<?php

namespace App\Observers;

use App\Models\InvoiceItem;
use App\Services\InvoiceCalculator;

class InvoiceItemObserver
{
    public function saving(InvoiceItem $item): void
    {
        $qty = (float) $item->quantity;
        $price = (float) $item->unit_price;
        $discountPct = (float) ($item->discount_pct ?? 0);
        $gross = $qty * $price;
        $afterDiscount = $gross * (1 - $discountPct / 100);
        $item->line_total = round($afterDiscount, 2);
    }

    public function saved(InvoiceItem $item): void
    {
        if ($item->invoice) {
            app(InvoiceCalculator::class)->recalculate($item->invoice->fresh());
        }
    }

    public function deleted(InvoiceItem $item): void
    {
        if ($item->invoice) {
            app(InvoiceCalculator::class)->recalculate($item->invoice->fresh());
        }
    }
}
