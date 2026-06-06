<x-mail::message>
# Balasan Tiket #{{ $reply->ticket->number }}

Halo {{ $reply->ticket->contact?->full_name ?? $reply->ticket->client->company_name }},

Ada balasan baru pada tiket **#{{ $reply->ticket->number }}: {{ $reply->ticket->subject }}**:

<x-mail::panel>
{{ $reply->body }}
</x-mail::panel>

<x-mail::button :url="route('portal.ticket.show', $reply->ticket->public_token)">
Lihat Tiket
</x-mail::button>

Terima kasih.

{{ config('app.name') }}
</x-mail::message>
