<?php

namespace App\Adapters\Mail;

use App\Models\Provider;
use Illuminate\Mail\Mailer;
use Illuminate\Mail\Transport\SmtpTransport;
use Symfony\Component\Mailer\Transport\Dsn;
use Symfony\Component\Mailer\Transport\Smtp\EsmtpTransport;
use Symfony\Component\Mailer\Transport\Smtp\EsmtpTransportFactory;
use Symfony\Component\Mime\Email;

/**
 * Generic SMTP mail adapter.
 *
 * Owner inputs in admin: host, port, username, password, encryption (tls/ssl/none), from_address, from_name.
 * Compatible with any SMTP server: Mailgun, Postmark, SendGrid, SES, custom Postfix, etc.
 */
class SmtpAdapter implements MailAdapterContract
{
    public function __construct(private Provider $provider) {}

    public function send(string $to, string $subject, string $html, ?string $text = null, array $options = []): bool
    {
        $config = $this->provider->extra_config ?? [];

        $dsn = sprintf(
            '%s://%s:%s@%s:%d',
            $config['encryption'] ?? 'smtp',
            urlencode($config['username'] ?? ''),
            urlencode($this->provider->api_key ?? ''),
            $config['host'] ?? 'localhost',
            (int) ($config['port'] ?? 587)
        );

        $factory = new EsmtpTransportFactory();
        $transport = $factory->create(Dsn::fromString($dsn));

        $email = (new Email())
            ->from(($config['from_name'] ?? 'crmoffice') . ' <' . ($config['from_address'] ?? 'noreply@example.com') . '>')
            ->to($to)
            ->subject($subject)
            ->html($html);

        if ($text) {
            $email->text($text);
        }

        try {
            $transport->send($email);

            return true;
        } catch (\Throwable $e) {
            logger()->error('SMTP send failed', ['provider' => $this->provider->id, 'error' => $e->getMessage()]);

            return false;
        }
    }

    public function probeConnection(): array
    {
        try {
            $config = $this->provider->extra_config ?? [];
            $errno = 0;
            $errstr = '';
            $start = microtime(true);
            $fp = @fsockopen($config['host'] ?? 'localhost', (int) ($config['port'] ?? 587), $errno, $errstr, 5);
            $latency = (int) ((microtime(true) - $start) * 1000);

            if ($fp) {
                fclose($fp);

                return ['success' => true, 'latency_ms' => $latency];
            }

            return ['success' => false, 'error' => $errstr ?: "Cannot connect to host:port"];
        } catch (\Throwable $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
}
