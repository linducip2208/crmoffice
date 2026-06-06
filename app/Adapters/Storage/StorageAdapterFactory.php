<?php

namespace App\Adapters\Storage;

use App\Models\Provider;
use InvalidArgumentException;

class StorageAdapterFactory
{
    public static function for(Provider $provider): StorageAdapterContract
    {
        return match ($provider->api_format) {
            's3_compatible' => new S3CompatibleAdapter($provider),
            'local' => new LocalAdapter($provider),
            default => throw new InvalidArgumentException("Unsupported storage format: {$provider->api_format}"),
        };
    }

    public static function active(): ?StorageAdapterContract
    {
        $provider = Provider::where('type', 'storage')
            ->where('is_active', true)
            ->orderBy('priority')
            ->first();

        return $provider ? self::for($provider) : null;
    }
}
