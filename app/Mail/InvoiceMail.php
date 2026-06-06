<?php

namespace App\Mail;

use App\Models\Invoice;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class InvoiceMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public Invoice $invoice,
        public string $subjectLine = '',
        public string $extraMessage = '',
        public ?string $pdfPath = null,
        public string $locale = 'id',
    ) {}

    public function envelope(): Envelope
    {
        $prefix = $this->locale === 'en' ? "Invoice #{$this->invoice->number}" : "Invoice #{$this->invoice->number}";

        return new Envelope(
            subject: $this->subjectLine ?: $prefix,
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'mail.' . $this->locale . '.invoice',
            with: [
                'invoice' => $this->invoice,
                'message' => $this->extraMessage,
            ],
        );
    }

    public function attachments(): array
    {
        if ($this->pdfPath && file_exists($this->pdfPath)) {
            return [
                Attachment::fromPath($this->pdfPath)
                    ->as("Invoice-{$this->invoice->number}.pdf")
                    ->withMime('application/pdf'),
            ];
        }

        return [];
    }
}
