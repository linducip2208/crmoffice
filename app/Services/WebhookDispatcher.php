<?php

namespace App\Services;

use App\Jobs\DispatchWebhook;
use App\Models\Webhook;

class WebhookDispatcher
{
    public function fire(string $event, array $payload): void
    {
        Webhook::where('event', $event)
            ->where('is_active', true)
            ->get()
            ->each(fn ($w) => DispatchWebhook::dispatch($w->id, $event, $payload));
    }
}
