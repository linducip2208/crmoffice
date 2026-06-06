<?php

namespace App\Notifications;

use App\Models\Ticket;
use App\Notifications\Concerns\RespectsPreferences;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TicketAssignedNotification extends Notification
{
    use Queueable, RespectsPreferences;

    public function __construct(public Ticket $ticket) {}

    public function eventKey(): string
    {
        return 'ticket.assigned';
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'title' => "Ticket {$this->ticket->number} assigned to you",
            'body' => $this->ticket->subject,
            'url' => "/admin/tickets/{$this->ticket->id}/edit",
            'icon' => 'heroicon-o-lifebuoy',
            'ticket_id' => $this->ticket->id,
        ];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject("Ticket {$this->ticket->number} assigned to you")
            ->line($this->ticket->subject)
            ->action('Open ticket', url("/admin/tickets/{$this->ticket->id}/edit"));
    }
}
