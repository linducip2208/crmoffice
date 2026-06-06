<?php

namespace App\Mail;

use App\Models\Estimate;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class EstimateMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public Estimate $estimate,
        public string $subjectLine = '',
        public string $extraMessage = '',
        public string $locale = 'id',
    ) {}

    public function envelope(): Envelope
    {
        $prefix = $this->locale === 'en' ? "Estimate #{$this->estimate->number}" : "Estimasi #{$this->estimate->number}";

        return new Envelope(
            subject: $this->subjectLine ?: $prefix,
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'mail.' . $this->locale . '.estimate',
            with: [
                'estimate' => $this->estimate,
                'message' => $this->extraMessage,
            ],
        );
    }
}
