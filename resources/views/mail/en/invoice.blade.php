<x-mail::message>
# Invoice #{{ $invoice->number }}

Hello {{ $invoice->client->company_name }},

Attached is invoice **#{{ $invoice->number }}** with a total of **{{ number_format($invoice->total, 0, ',', '.') }}** {{ $invoice->currency?->code ?? 'IDR' }}.

**Due Date:** {{ $invoice->due_date->format('d M Y') }}

{{ $message ?? '' }}

<x-mail::button :url="route('portal.invoice.show', $invoice->public_token)">
View Invoice
</x-mail::button>

Thank you for your business.

{{ config('app.name') }}
</x-mail::message>
