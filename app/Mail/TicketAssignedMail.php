<?php

namespace App\Mail;

use App\Models\Ticket;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class TicketAssignedMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public Ticket $ticket,
        public string $locale = 'id',
    ) {}

    public function envelope(): Envelope
    {
        $subject = $this->locale === 'en'
            ? "Ticket #{$this->ticket->number} Assigned — {$this->ticket->subject}"
            : "Tiket #{$this->ticket->number} Ditugaskan — {$this->ticket->subject}";

        return new Envelope(
            subject: $subject,
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'mail.' . $this->locale . '.ticket-assigned',
        );
    }
}
