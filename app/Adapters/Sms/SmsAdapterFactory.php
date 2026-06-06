<?php

namespace App\Adapters\Sms;

use App\Models\Provider;
use InvalidArgumentException;

class SmsAdapterFactory
{
    public static function for(Provider $provider): SmsAdapterContract
    {
        return match ($provider->api_format) {
            'rest_sms' => new RestSmsAdapter($provider),
            default => throw new InvalidArgumentException("Unsupported SMS format: {$provider->api_format}"),
        };
    }

    public static function active(): ?SmsAdapterContract
    {
        $provider = Provider::where('type', 'sms')
            ->where('is_active', true)
            ->orderBy('priority')
            ->first();

        return $provider ? self::for($provider) : null;
    }
}
