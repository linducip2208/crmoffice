<?php

namespace App\Adapters\Mail;

use App\Models\Provider;
use InvalidArgumentException;

class MailAdapterFactory
{
    public static function for(Provider $provider): MailAdapterContract
    {
        return match ($provider->api_format) {
            'smtp' => new SmtpAdapter($provider),
            'rest_api' => new RestApiAdapter($provider),
            default => throw new InvalidArgumentException("Unsupported mail format: {$provider->api_format}"),
        };
    }

    public static function active(): ?MailAdapterContract
    {
        $provider = Provider::where('type', 'mail')
            ->where('is_active', true)
            ->orderBy('priority')
            ->first();

        return $provider ? self::for($provider) : null;
    }
}
