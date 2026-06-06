<?php

namespace App\Notifications;

use App\Models\Invoice;
use App\Notifications\Concerns\RespectsPreferences;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class InvoiceSentNotification extends Notification
{
    use Queueable, RespectsPreferences;

    public function __construct(public Invoice $invoice) {}

    public function eventKey(): string
    {
        return 'invoice.sent';
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'title' => "Invoice {$this->invoice->number} sent",
            'body' => "Client: {$this->invoice->client?->company_name} · Total: {$this->invoice->total}",
            'url' => "/admin/invoices/{$this->invoice->id}/edit",
            'icon' => 'heroicon-o-paper-airplane',
            'invoice_id' => $this->invoice->id,
        ];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject("Invoice {$this->invoice->number} has been sent")
            ->line("Invoice {$this->invoice->number} for {$this->invoice->client?->company_name} has been sent to the client.")
            ->line("Total: {$this->invoice->total}")
            ->action('View invoice', url("/admin/invoices/{$this->invoice->id}/edit"));
    }
}
