<?php

namespace App\Observers;

use App\Models\EstimateItem;
use App\Services\InvoiceCalculator;

class EstimateItemObserver
{
    public function saving(EstimateItem $item): void
    {
        $qty = (float) $item->quantity;
        $price = (float) $item->unit_price;
        $discountPct = (float) ($item->discount_pct ?? 0);
        $gross = $qty * $price;
        $afterDiscount = $gross * (1 - $discountPct / 100);
        $item->line_total = round($afterDiscount, 2);
    }

    public function saved(EstimateItem $item): void
    {
        if ($item->estimate) {
            app(InvoiceCalculator::class)->recalculateEstimate($item->estimate->fresh());
        }
    }

    public function deleted(EstimateItem $item): void
    {
        if ($item->estimate) {
            app(InvoiceCalculator::class)->recalculateEstimate($item->estimate->fresh());
        }
    }
}
