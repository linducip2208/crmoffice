<?php

namespace App\Adapters\Sms;

use App\Models\Provider;
use Illuminate\Support\Facades\Http;

/**
 * Generic REST SMS adapter.
 *
 * Owner configures send_endpoint + body_template + response paths in admin.
 * Compatible with Twilio REST, Vonage, MessageBird, Zenziva, MISMS, Africa's Talking, etc.
 */
class RestSmsAdapter implements SmsAdapterContract
{
    public function __construct(private Provider $provider) {}

    public function send(string $to, string $message): bool
    {
        $config = $this->provider->extra_config ?? [];
        $endpoint = rtrim($this->provider->base_url, '/') . ($config['send_endpoint'] ?? '/v1/messages');

        $template = $config['body_template'] ?? [
            'to' => '{{to}}',
            'from' => '{{from}}',
            'body' => '{{message}}',
        ];

        $vars = [
            'to' => $to,
            'from' => $config['from'] ?? 'crmoffice',
            'message' => $message,
        ];

        $body = $this->resolveTemplate($template, $vars);

        $headers = $this->provider->extra_headers ?? [];
        if ($this->provider->api_key) {
            $headers['Authorization'] = ($config['auth_scheme'] ?? 'Bearer') . ' ' . $this->provider->api_key;
        }

        try {
            $response = Http::withHeaders($headers)->acceptJson()->timeout(15)->post($endpoint, $body);

            return $response->successful();
        } catch (\Throwable $e) {
            logger()->error('SMS send failed', ['provider' => $this->provider->id, 'error' => $e->getMessage()]);

            return false;
        }
    }

    public function probeConnection(): array
    {
        try {
            $start = microtime(true);
            $response = Http::timeout(10)->get(rtrim($this->provider->base_url, '/'));

            return [
                'success' => $response->status() < 500,
                'http_status' => $response->status(),
                'latency_ms' => (int) ((microtime(true) - $start) * 1000),
            ];
        } catch (\Throwable $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    private function resolveTemplate(array $template, array $vars): array
    {
        $resolved = [];
        foreach ($template as $key => $value) {
            if (is_string($value)) {
                $resolved[$key] = preg_replace_callback('/\{\{(\w+)\}\}/', fn ($m) => (string) ($vars[$m[1]] ?? ''), $value);
            } elseif (is_array($value)) {
                $resolved[$key] = $this->resolveTemplate($value, $vars);
            } else {
                $resolved[$key] = $value;
            }
        }

        return $resolved;
    }
}
