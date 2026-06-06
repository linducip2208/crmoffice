<?php

namespace App\Mail;

use App\Models\TicketReply;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class TicketRepliedMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public TicketReply $reply,
        public string $locale = 'id',
    ) {}

    public function envelope(): Envelope
    {
        $subject = $this->locale === 'en'
            ? "New Reply on Ticket #{$this->reply->ticket->number} — {$this->reply->ticket->subject}"
            : "Balasan Baru Tiket #{$this->reply->ticket->number} — {$this->reply->ticket->subject}";

        return new Envelope(
            subject: $subject,
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'mail.' . $this->locale . '.ticket-replied',
        );
    }
}
