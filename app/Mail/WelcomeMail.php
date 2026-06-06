<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class WelcomeMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $name,
        public string $email,
        public string $password,
        public string $locale = 'id',
    ) {}

    public function envelope(): Envelope
    {
        $subject = $this->locale === 'en'
            ? 'Welcome to ' . config('app.name')
            : 'Selamat Datang di ' . config('app.name');

        return new Envelope(
            subject: $subject,
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'mail.' . $this->locale . '.welcome',
            with: [
                'name' => $this->name,
                'email' => $this->email,
                'password' => $this->password,
            ],
        );
    }
}
