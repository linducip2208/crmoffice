<?php

namespace App\Adapters\Payment\Dto;

class PaymentIntent
{
    public function __construct(
        public string $type, // 'redirect' | 'embed' | 'qr'
        public ?string $redirectUrl = null,
        public ?string $clientToken = null,
        public ?string $qrString = null,
        public ?string $qrImageUrl = null,
        public ?array $embedPayload = null,
        public ?string $reference = null,
        public ?int $expiresInSeconds = null,
    ) {}

    public function toArray(): array
    {
        return array_filter(get_object_vars($this), fn ($v) => $v !== null);
    }
}
