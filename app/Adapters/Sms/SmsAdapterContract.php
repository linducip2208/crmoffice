<?php

namespace App\Adapters\Sms;

use App\Models\Provider;

interface SmsAdapterContract
{
    public function __construct(Provider $provider);

    public function send(string $to, string $message): bool;

    public function probeConnection(): array;
}
