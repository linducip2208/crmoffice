<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\Setting;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class InvoicePdfController extends Controller
{
    public function download(Invoice $invoice, Request $request): Response
    {
        $invoice->load(['client', 'currency', 'items.taxRate', 'items.item', 'payments']);

        $locale = $request->query('locale', 'id');

        $pdf = Pdf::loadView('pdf.' . $locale . '.invoice', [
            'invoice' => $invoice,
            'appName' => Setting::get('app_name', 'crmoffice'),
        ])->setPaper('a4');

        return $pdf->download("invoice-{$invoice->number}.pdf");
    }

    public function stream(Invoice $invoice, Request $request): Response
    {
        $invoice->load(['client', 'currency', 'items.taxRate', 'items.item', 'payments']);

        $locale = $request->query('locale', 'id');

        $pdf = Pdf::loadView('pdf.' . $locale . '.invoice', [
            'invoice' => $invoice,
            'appName' => Setting::get('app_name', 'crmoffice'),
        ])->setPaper('a4');

        return $pdf->stream("invoice-{$invoice->number}.pdf");
    }
}
