<x-mail::message>
# Estimasi #{{ $estimate->number }}

Halo {{ $estimate->client->company_name }},

Terlampir estimasi **#{{ $estimate->number }}** dengan total **{{ number_format($estimate->total, 0, ',', '.') }}** {{ $estimate->currency?->code ?? 'IDR' }}.

**Berlaku Hingga:** {{ $estimate->expiry_date->format('d M Y') }}

{{ $message ?? '' }}

<x-mail::button :url="route('portal.estimate.show', $estimate->public_token)">
Lihat Estimasi
</x-mail::button>

Terima kasih.

{{ config('app.name') }}
</x-mail::message>
