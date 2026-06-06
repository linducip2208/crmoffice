<?php

namespace App\Jobs;

use App\Models\Webhook;
use App\Models\WebhookDelivery;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class DispatchWebhook implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 5;

    public function __construct(public int $webhookId, public string $event, public array $payload) {}

    public function backoff(): array
    {
        return [60, 300, 900, 3600, 21600];
    }

    public function handle(): void
    {
        $webhook = Webhook::find($this->webhookId);
        if (! $webhook || ! $webhook->is_active) {
            return;
        }

        $body = json_encode([
            'event' => $this->event,
            'id' => 'evt_' . Str::ulid(),
            'occurred_at' => now()->toIso8601String(),
            'data' => $this->payload,
        ]);

        $signature = hash_hmac('sha256', $body, $webhook->secret);

        $delivery = WebhookDelivery::create([
            'webhook_id' => $webhook->id,
            'payload' => json_decode($body, true),
            'attempt' => $this->attempts(),
        ]);

        try {
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
                'X-Crmoffice-Event' => $this->event,
                'X-Crmoffice-Signature' => "sha256=$signature",
                'X-Crmoffice-Delivery' => $delivery->id,
            ])->withBody($body, 'application/json')->timeout(15)->post($webhook->url);

            $delivery->update([
                'status_code' => $response->status(),
                'response_body' => substr((string) $response->body(), 0, 5000),
                'delivered_at' => now(),
            ]);

            if (! $response->successful()) {
                $this->release($this->backoff()[$this->attempts() - 1] ?? 3600);
            }
        } catch (\Throwable $e) {
            $delivery->update(['response_body' => substr($e->getMessage(), 0, 5000)]);
            $this->release($this->backoff()[$this->attempts() - 1] ?? 3600);
        }
    }
}
