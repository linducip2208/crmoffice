<?php

namespace App\Services;

use App\Models\Payment;
use App\Models\Setting;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Response;

class ReceiptPdfService
{
    public function download(Payment $payment, string $locale = 'id'): Response
    {
        $payment->load(['invoice.client', 'invoice.currency', 'currency']);

        $pdf = Pdf::loadView('pdf.' . $locale . '.receipt', [
            'payment' => $payment,
            'appName' => Setting::get('app_name', 'crmoffice'),
        ])->setPaper('a5');

        return $pdf->download("receipt-{$payment->id}.pdf");
    }

    public function stream(Payment $payment, string $locale = 'id'): Response
    {
        $payment->load(['invoice.client', 'invoice.currency', 'currency']);

        $pdf = Pdf::loadView('pdf.' . $locale . '.receipt', [
            'payment' => $payment,
            'appName' => Setting::get('app_name', 'crmoffice'),
        ])->setPaper('a5');

        return $pdf->stream("receipt-{$payment->id}.pdf");
    }
}
