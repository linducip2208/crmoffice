<?php

namespace App\Adapters\Payment;

use App\Adapters\Payment\Dto\ParsedPayment;
use App\Adapters\Payment\Dto\PaymentIntent;
use App\Models\Invoice;
use App\Models\Provider;
use Illuminate\Http\Request;

interface PaymentAdapterContract
{
    public function __construct(Provider $provider);

    public function createIntent(Invoice $invoice, array $options = []): PaymentIntent;

    public function verifyCallback(Request $request): ?ParsedPayment;

    public function probeConnection(): array;
}
