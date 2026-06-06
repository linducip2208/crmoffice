<?php

namespace App\Adapters\Mail;

use App\Models\Provider;
use Illuminate\Support\Facades\Http;

/**
 * Generic REST mail adapter.
 *
 * Owner configures body_template + endpoint in admin. Compatible with Mailgun API,
 * Postmark, SendGrid, SES API, Resend, etc. — anything HTTP-based.
 */
class RestApiAdapter implements MailAdapterContract
{
    public function __construct(private Provider $provider) {}

    public function send(string $to, string $subject, string $html, ?string $text = null, array $options = []): bool
    {
        $config = $this->provider->extra_config ?? [];
        $endpoint = rtrim($this->provider->base_url, '/') . ($config['send_endpoint'] ?? '/v3/send');

        $template = $config['body_template'] ?? [
            'from' => '{{from_address}}',
            'to' => '{{to}}',
            'subject' => '{{subject}}',
            'html' => '{{html}}',
            'text' => '{{text}}',
        ];

        $vars = [
            'from_address' => $config['from_address'] ?? 'noreply@example.com',
            'from_name' => $config['from_name'] ?? 'crmoffice',
            'to' => $to,
            'subject' => $subject,
            'html' => $html,
            'text' => $text ?? strip_tags($html),
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
            logger()->error('REST mail send failed', ['provider' => $this->provider->id, 'error' => $e->getMessage()]);

            return false;
        }
    }

    public function probeConnection(): array
    {
        try {
            $start = microtime(true);
            $response = Http::withHeaders(['Authorization' => 'Bearer ' . $this->provider->api_key])
                ->timeout(10)
                ->get(rtrim($this->provider->base_url, '/'));

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
