<x-mail::message>
# Ticket Reply #{{ $reply->ticket->number }}

Hello {{ $reply->ticket->contact?->full_name ?? $reply->ticket->client->company_name }},

There is a new reply on ticket **#{{ $reply->ticket->number }}: {{ $reply->ticket->subject }}**:

<x-mail::panel>
{{ $reply->body }}
</x-mail::panel>

<x-mail::button :url="route('portal.ticket.show', $reply->ticket->public_token)">
View Ticket
</x-mail::button>

Thank you.

{{ config('app.name') }}
</x-mail::message>
