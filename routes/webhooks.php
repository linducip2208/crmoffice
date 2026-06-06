<?php

use App\Http\Controllers\Webhook\InboundEmailWebhookController;
use App\Http\Controllers\Webhook\LeadWebhookController;
use App\Http\Controllers\Webhook\PaymentWebhookController;
use Illuminate\Support\Facades\Route;

Route::prefix('webhooks')
    ->name('webhooks.')
    ->middleware(['api', 'throttle:webhook'])
    ->group(function () {
        Route::post('/payment/{providerId}', [PaymentWebhookController::class, 'handle'])
            ->whereNumber('providerId')
            ->name('payment');

        Route::post('/inbound-email/{token}', [InboundEmailWebhookController::class, 'handle'])
            ->where('token', '[a-zA-Z0-9_-]{16,64}')
            ->name('email');

        Route::post('/lead/{token}', [LeadWebhookController::class, 'handle'])
            ->where('token', '[a-zA-Z0-9_-]{16,64}')
            ->name('lead');
    });
