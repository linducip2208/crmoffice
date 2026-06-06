<?php

namespace App\Adapters\Payment;

use App\Adapters\Payment\Dto\ParsedPayment;
use App\Adapters\Payment\Dto\PaymentIntent;
use App\Models\Invoice;
use App\Models\Provider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

/**
 * Generic QR-flow payment adapter (QRIS, PromptPay, PayNow, VietQR aggregators).
 * Returns QR string + image URL for display; verified via webhook or poll.
 */
class QrFlowAdapter implements PaymentAdapterContract
{
    public function __construct(private Provider $provider) {}

    public function createIntent(Invoice $invoice, array $options = []): PaymentIntent
    {
        $config = $this->provider->extra_config ?? [];
        $endpoint = rtrim($this->provider->base_url, '/') . ($config['create_qr_endpoint'] ?? '/v1/qr');

        $response = Http::withHeaders($this->buildHeaders())
            ->acceptJson()
            ->timeout(15)
            ->post($endpoint, [
                'amount' => (int) $invoice->balance_due,
                'currency' => $invoice->currency?->code ?? 'IDR',
                'reference' => $invoice->number,
                'expires_in' => $config['expires_in'] ?? 900,
            ]);

        $data = $response->json() ?? [];

        return new PaymentIntent(
            type: 'qr',
            qrString: data_get($data, $config['qr_string_path'] ?? 'qr_string'),
            qrImageUrl: data_get($data, $config['qr_image_path'] ?? 'qr_image_url'),
            reference: $invoice->number,
            expiresInSeconds: data_get($data, $config['expires_in_path'] ?? 'expires_in', 900),
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

        $statusValue = (string) data_get($payload, $config['status_field_path'] ?? 'status');
        $status = match (strtolower($statusValue)) {
            'settled', 'paid', 'success', 'completed' => 'settled',
            'pending', 'waiting' => 'pending',
            default => 'failed',
        };

        return new ParsedPayment(
            invoiceReference: (string) data_get($payload, $config['reference_field_path'] ?? 'reference'),
            amount: (float) data_get($payload, $config['amount_field_path'] ?? 'amount'),
            currencyCode: (string) data_get($payload, $config['currency_field_path'] ?? 'currency') ?: 'IDR',
            status: $status,
            transactionId: data_get($payload, $config['transaction_id_path'] ?? 'transaction_id'),
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
