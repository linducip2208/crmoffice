<?php

namespace App\Adapters\Payment\Dto;

class ParsedPayment
{
    public function __construct(
        public string $invoiceReference,
        public float $amount,
        public string $currencyCode,
        public string $status, // 'settled' | 'pending' | 'failed'
        public ?string $transactionId = null,
        public ?array $rawPayload = null,
    ) {}
}
