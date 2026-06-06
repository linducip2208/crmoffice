<x-mail::message>
# Estimate #{{ $estimate->number }}

Hello {{ $estimate->client->company_name }},

Attached is estimate **#{{ $estimate->number }}** with a total of **{{ number_format($estimate->total, 0, ',', '.') }}** {{ $estimate->currency?->code ?? 'IDR' }}.

**Valid Until:** {{ $estimate->expiry_date->format('d M Y') }}

{{ $message ?? '' }}

<x-mail::button :url="route('portal.estimate.show', $estimate->public_token)">
View Estimate
</x-mail::button>

Thank you.

{{ config('app.name') }}
</x-mail::message>
