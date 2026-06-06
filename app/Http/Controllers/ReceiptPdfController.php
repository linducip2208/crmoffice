<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Services\ReceiptPdfService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class ReceiptPdfController extends Controller
{
    public function download(Payment $payment, ReceiptPdfService $service, Request $request): Response
    {
        $locale = $request->query('locale', 'id');

        return $service->download($payment, $locale);
    }
}
