<x-mail::message>
# Invoice #{{ $invoice->number }}

Halo {{ $invoice->client->company_name }},

Terlampir invoice **#{{ $invoice->number }}** dengan total **{{ number_format($invoice->total, 0, ',', '.') }}** {{ $invoice->currency?->code ?? 'IDR' }}.

**Batas Pembayaran:** {{ $invoice->due_date->format('d M Y') }}

{{ $message ?? '' }}

<x-mail::button :url="route('portal.invoice.show', $invoice->public_token)">
Lihat Invoice
</x-mail::button>

Terima kasih atas kepercayaan Anda.

{{ config('app.name') }}
</x-mail::message>
