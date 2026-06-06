<?php

namespace App\Adapters\Mail;

use App\Models\Provider;

interface MailAdapterContract
{
    public function __construct(Provider $provider);

    public function send(string $to, string $subject, string $html, ?string $text = null, array $options = []): bool;

    public function probeConnection(): array;
}
