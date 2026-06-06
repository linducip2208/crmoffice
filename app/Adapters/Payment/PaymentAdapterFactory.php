<?php

namespace App\Adapters\Payment;

use App\Models\Provider;
use InvalidArgumentException;

class PaymentAdapterFactory
{
    public static function for(Provider $provider): PaymentAdapterContract
    {
        return match ($provider->api_format) {
            'redirect_flow' => new RedirectFlowAdapter($provider),
            'embed_flow' => new EmbedFlowAdapter($provider),
            'qr_flow' => new QrFlowAdapter($provider),
            default => throw new InvalidArgumentException("Unsupported payment format: {$provider->api_format}"),
        };
    }

    public static function active(): ?PaymentAdapterContract
    {
        $provider = Provider::where('type', 'payment')
            ->where('is_active', true)
            ->orderBy('priority')
            ->first();

        return $provider ? self::for($provider) : null;
    }
}
