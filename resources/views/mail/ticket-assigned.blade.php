<x-mail::message>
# Tiket #{{ $ticket->number }} — Ditugaskan

Halo {{ $ticket->assignee->name }},

Tiket **#{{ $ticket->number }}: {{ $ticket->subject }}** telah ditugaskan kepada Anda.

**Klien:** {{ $ticket->client->company_name }}
**Prioritas:** {{ $ticket->priority->name }}
**Batas Respons:** {{ $ticket->first_response_due_at?->format('d M Y H:i') }}

<x-mail::button :url="route('filament.admin.resources.tickets.edit', $ticket)">
Lihat Tiket
</x-mail::button>

{{ config('app.name') }}
</x-mail::message>
