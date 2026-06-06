<x-mail::message>
# Ticket #{{ $ticket->number }} — Assigned

Hello {{ $ticket->assignee->name }},

Ticket **#{{ $ticket->number }}: {{ $ticket->subject }}** has been assigned to you.

**Client:** {{ $ticket->client->company_name }}
**Priority:** {{ $ticket->priority->name }}
**First Response Due:** {{ $ticket->first_response_due_at?->format('d M Y H:i') }}

<x-mail::button :url="route('filament.admin.resources.tickets.edit', $ticket)">
View Ticket
</x-mail::button>

{{ config('app.name') }}
</x-mail::message>
