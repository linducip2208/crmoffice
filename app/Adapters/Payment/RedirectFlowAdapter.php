<?php

namespace App\Adapters\Payment;

use App\Adapters\Payment\Dto\ParsedPayment;
use App\Adapters\Payment\Dto\PaymentIntent;
use App\Models\Invoice;
use App\Models\Provider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

/**
 * Generic redirect-flow payment adapter.
 *
 * Reads endpoints + field mappings from $provider->extra_config (set by owner in admin UI).
 * Compatible with: Midtrans Snap, Xendit Invoice, Stripe Checkout, PayPal Standard,
 * Razorpay Hosted, Doku Checkout, Faspay, etc. — owner picks/edits preset, never hardcoded.
 */
class RedirectFlowAdapter implements PaymentAdapterContract
{
    public function __construct(private Provider $provider) {}

    public function createIntent(Invoice $invoice, array $options = []): PaymentIntent
    {
        $config = $this->provider->extra_config ?? [];
        $endpoint = rtrim($this->provider->base_url, '/') . ($config['create_intent_endpoint'] ?? '/v1/payments');

        $bodyTemplate = $config['body_template'] ?? [
            'amount' => '{{amount}}',
            'currency' => '{{currency}}',
            'reference' => '{{reference}}',
            'success_url' => '{{success_url}}',
            'failure_url' => '{{failure_url}}',
        ];

        $vars = [
            'amount' => (int) $invoice->balance_due,
            'currency' => $invoice->currency?->code ?? 'IDR',
            'reference' => $invoice->number,
            'invoice_id' => $invoice->id,
            'success_url' => $options['return_url'] ?? url("/public/invoices/{$invoice->public_token}"),
            'failure_url' => $options['cancel_url'] ?? url("/public/invoices/{$invoice->public_token}"),
            'customer_name' => $invoice->client?->company_name,
            'customer_email' => $invoice->client?->contacts?->first()?->email,
        ];

        $body = $this->resolveTemplate($bodyTemplate, $vars);

        $response = Http::withHeaders($this->buildHeaders())
            ->acceptJson()
            ->timeout(15)
            ->post($endpoint, $body);

        $data = $response->json() ?? [];
        $redirectUrl = data_get($data, $config['response_redirect_path'] ?? 'redirect_url');

        return new PaymentIntent(
            type: 'redirect',
            redirectUrl: $redirectUrl,
            reference: $invoice->number,
            expiresInSeconds: $config['expires_in'] ?? 3600,
        );
    }

    public function verifyCallback(Request $request): ?ParsedPayment
    {
        $config = $this->provider->extra_config ?? [];
        $payload = $request->all();

        // Verify HMAC signature if configured
        if (! empty($config['callback_signature_header'])) {
            $signature = $request->header($config['callback_signature_header']);
            $algo = $config['callback_signature_algo'] ?? 'sha256';
            $secret = $config['webhook_secret'] ?? $this->provider->api_key;
            $expected = hash_hmac($algo, $request->getContent(), (string) $secret);
            if (! hash_equals($expected, (string) $signature)) {
                return null;
            }
        }

        $statusValue = data_get($payload, $config['status_field_path'] ?? 'status');
        $statusMap = $config['status_map'] ?? [
            'settled' => 'settled', 'success' => 'settled', 'paid' => 'settled', 'captured' => 'settled',
            'pending' => 'pending', 'authorized' => 'pending',
            'failed' => 'failed', 'expired' => 'failed', 'cancelled' => 'failed',
        ];
        $status = $statusMap[$statusValue] ?? 'pending';

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
            $config = $this->provider->extra_config ?? [];
            $endpoint = rtrim($this->provider->base_url, '/') . ($config['health_endpoint'] ?? '/');
            $start = microtime(true);
            $response = Http::withHeaders($this->buildHeaders())->timeout(10)->get($endpoint);
            $latency = (int) ((microtime(true) - $start) * 1000);

            return [
                'success' => $response->successful() || $response->status() < 500,
                'http_status' => $response->status(),
                'latency_ms' => $latency,
            ];
        } catch (\Throwable $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    private function buildHeaders(): array
    {
        $headers = $this->provider->extra_headers ?? [];
        if ($this->provider->api_key) {
            $authHeader = $this->provider->extra_config['auth_header'] ?? 'Authorization';
            $authScheme = $this->provider->extra_config['auth_scheme'] ?? 'Bearer';
            $headers[$authHeader] = trim("$authScheme {$this->provider->api_key}");
        }

        return $headers;
    }

    private function resolveTemplate(array $template, array $vars): array
    {
        $resolved = [];
        foreach ($template as $key => $value) {
            if (is_string($value)) {
                $resolved[$key] = preg_replace_callback('/\{\{(\w+)\}\}/', fn ($m) => $vars[$m[1]] ?? '', $value);
            } elseif (is_array($value)) {
                $resolved[$key] = $this->resolveTemplate($value, $vars);
            } else {
                $resolved[$key] = $value;
            }
        }

        return $resolved;
    }
}
