<?php

namespace App\Notifications;

use App\Models\Invoice;
use App\Notifications\Concerns\RespectsPreferences;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class InvoiceOverdueNotification extends Notification
{
    use Queueable, RespectsPreferences;

    public function __construct(public Invoice $invoice) {}

    public function eventKey(): string
    {
        return 'invoice.overdue';
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'title' => "Invoice {$this->invoice->number} is overdue",
            'body' => "Balance due: {$this->invoice->balance_due}",
            'url' => "/admin/invoices/{$this->invoice->id}/edit",
            'icon' => 'heroicon-o-exclamation-triangle',
            'invoice_id' => $this->invoice->id,
        ];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject("Invoice {$this->invoice->number} is overdue")
            ->line("Invoice {$this->invoice->number} for {$this->invoice->client?->company_name} is past due.")
            ->line("Balance due: {$this->invoice->balance_due}")
            ->action('View invoice', url("/admin/invoices/{$this->invoice->id}/edit"));
    }
}
