<?php

namespace App\Console\Commands;

use App\Jobs\DispatchWebhook;
use App\Models\WebhookDelivery;
use Illuminate\Console\Command;

class ReplayWebhookDelivery extends Command
{
    protected $signature = 'crmoffice:webhook-replay {delivery_id}';

    protected $description = 'Re-dispatch a webhook delivery by ID.';

    public function handle(): int
    {
        $delivery = WebhookDelivery::with('webhook')->find((int) $this->argument('delivery_id'));
        if (! $delivery || ! $delivery->webhook) {
            $this->error('Delivery not found.');

            return self::FAILURE;
        }

        $payload = $delivery->payload['data'] ?? $delivery->payload ?? [];
        $event = $delivery->payload['event'] ?? $delivery->webhook->event;

        DispatchWebhook::dispatch($delivery->webhook->id, $event, $payload);

        $this->info("Re-dispatched delivery #{$delivery->id} (event {$event}).");

        return self::SUCCESS;
    }
}
