<?php

namespace App\Adapters\Payment;

use App\Adapters\Payment\Dto\ParsedPayment;
use App\Adapters\Payment\Dto\PaymentIntent;
use App\Models\Invoice;
use App\Models\Provider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

/**
 * Generic embed-flow payment adapter (Stripe Elements / Braintree / Adyen style).
 * Returns client token + embed script URL for inline gateway widget.
 */
class EmbedFlowAdapter implements PaymentAdapterContract
{
    public function __construct(private Provider $provider) {}

    public function createIntent(Invoice $invoice, array $options = []): PaymentIntent
    {
        $config = $this->provider->extra_config ?? [];
        $endpoint = rtrim($this->provider->base_url, '/') . ($config['client_token_endpoint'] ?? '/v1/payment_intents');

        $response = Http::withHeaders($this->buildHeaders())
            ->acceptJson()
            ->timeout(15)
            ->post($endpoint, [
                'amount' => (int) $invoice->balance_due,
                'currency' => $invoice->currency?->code ?? 'IDR',
                'metadata' => ['invoice_number' => $invoice->number, 'invoice_id' => $invoice->id],
            ]);

        $data = $response->json() ?? [];

        return new PaymentIntent(
            type: 'embed',
            clientToken: data_get($data, $config['client_token_path'] ?? 'client_secret'),
            embedPayload: [
                'script_url' => $config['embed_script_url'] ?? null,
                'public_key' => $config['public_key'] ?? null,
                'token' => data_get($data, $config['client_token_path'] ?? 'client_secret'),
            ],
            reference: $invoice->number,
        );
    }

    public function verifyCallback(Request $request): ?ParsedPayment
    {
        $config = $this->provider->extra_config ?? [];
        $payload = $request->all();

        if (! empty($config['callback_signature_header'])) {
            $signature = $request->header($config['callback_signature_header']);
            $algo = $config['callback_signature_algo'] ?? 'sha256';
            $secret = $config['webhook_secret'] ?? $this->provider->api_key;
            $expected = hash_hmac($algo, $request->getContent(), (string) $secret);
            if (! hash_equals($expected, (string) $signature)) {
                return null;
            }
        }

        $status = (string) data_get($payload, $config['status_field_path'] ?? 'type');
        $status = str_contains($status, 'succeed') || str_contains($status, 'success') ? 'settled' : (str_contains($status, 'fail') ? 'failed' : 'pending');

        return new ParsedPayment(
            invoiceReference: (string) data_get($payload, $config['reference_field_path'] ?? 'data.object.metadata.invoice_number'),
            amount: (float) data_get($payload, $config['amount_field_path'] ?? 'data.object.amount'),
            currencyCode: (string) data_get($payload, $config['currency_field_path'] ?? 'data.object.currency') ?: 'IDR',
            status: $status,
            transactionId: data_get($payload, $config['transaction_id_path'] ?? 'data.object.id'),
            rawPayload: $payload,
        );
    }

    public function probeConnection(): array
    {
        try {
            $endpoint = rtrim($this->provider->base_url, '/') . '/';
            $start = microtime(true);
            $response = Http::withHeaders($this->buildHeaders())->timeout(10)->get($endpoint);

            return ['success' => $response->status() < 500, 'http_status' => $response->status(), 'latency_ms' => (int) ((microtime(true) - $start) * 1000)];
        } catch (\Throwable $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    private function buildHeaders(): array
    {
        $headers = $this->provider->extra_headers ?? [];
        if ($this->provider->api_key) {
            $headers['Authorization'] = "Bearer {$this->provider->api_key}";
        }

        return $headers;
    }
}
