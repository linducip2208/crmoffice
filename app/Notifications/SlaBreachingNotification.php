<?php

namespace App\Notifications;

use App\Models\Ticket;
use App\Notifications\Concerns\RespectsPreferences;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SlaBreachingNotification extends Notification
{
    use Queueable, RespectsPreferences;

    public function __construct(public Ticket $ticket, public string $type = 'first_response') {}

    public function eventKey(): string
    {
        return 'ticket.sla_breaching';
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'title' => "SLA breaching: ticket {$this->ticket->number}",
            'body' => ucfirst(str_replace('_', ' ', $this->type)).' SLA approaching breach.',
            'url' => "/admin/tickets/{$this->ticket->id}/edit",
            'icon' => 'heroicon-o-fire',
            'ticket_id' => $this->ticket->id,
            'type' => $this->type,
        ];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject("SLA breaching: ticket {$this->ticket->number}")
            ->error()
            ->line('A ticket is about to breach its SLA.')
            ->line("Subject: {$this->ticket->subject}")
            ->action('Open ticket', url("/admin/tickets/{$this->ticket->id}/edit"));
    }
}
