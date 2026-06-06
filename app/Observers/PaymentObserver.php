<?php

namespace App\Observers;

use App\Models\Payment;
use App\Services\InvoiceCalculator;

class PaymentObserver
{
    public function saved(Payment $payment): void
    {
        if ($payment->invoice) {
            app(InvoiceCalculator::class)->recalculate($payment->invoice->fresh());
        }
    }

    public function deleted(Payment $payment): void
    {
        if ($payment->invoice) {
            app(InvoiceCalculator::class)->recalculate($payment->invoice->fresh());
        }
    }
}
