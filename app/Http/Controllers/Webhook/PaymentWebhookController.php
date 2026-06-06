<?php

namespace App\Http\Controllers\Webhook;

use App\Actions\Sales\ApplyPaymentToInvoice;
use App\Adapters\Payment\PaymentAdapterFactory;
use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\Provider;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PaymentWebhookController extends Controller
{
    public function handle(Request $request, int $providerId): JsonResponse
    {
        $provider = Provider::where('id', $providerId)
            ->where('type', 'payment')
            ->where('is_active', true)
            ->first();

        if (! $provider) {
            return response()->json(['error' => 'Unknown provider'], 404);
        }

        try {
            $adapter = PaymentAdapterFactory::for($provider);
        } catch (\Throwable $e) {
            return response()->json(['error' => 'Adapter init failed: ' . $e->getMessage()], 500);
        }

        $parsed = $adapter->verifyCallback($request);
        if (! $parsed) {
            return response()->json(['error' => 'Signature verification failed'], 401);
        }

        $invoice = Invoice::where('number', $parsed->invoiceReference)->first();
        if (! $invoice) {
            return response()->json(['error' => 'Invoice not found'], 404);
        }

        if ($parsed->status === 'settled') {
            app(ApplyPaymentToInvoice::class)->handle($invoice, [
                'amount' => $parsed->amount,
                'method' => 'gateway',
                'provider_id' => $provider->id,
                'transaction_id' => $parsed->transactionId,
                'raw_payload' => $parsed->rawPayload,
            ]);
        }

        return response()->json(['ok' => true, 'status' => $parsed->status]);
    }
}
