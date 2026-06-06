<?php

namespace App\Notifications;

use App\Models\Invoice;
use App\Models\Payment;
use App\Notifications\Concerns\RespectsPreferences;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PaymentReceivedNotification extends Notification
{
    use Queueable, RespectsPreferences;

    public function __construct(public Invoice $invoice, public Payment $payment) {}

    public function eventKey(): string
    {
        return 'payment.received';
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'title' => "Payment received for {$this->invoice->number}",
            'body' => "Amount: {$this->payment->amount}",
            'url' => "/admin/invoices/{$this->invoice->id}/edit",
            'icon' => 'heroicon-o-banknotes',
            'invoice_id' => $this->invoice->id,
            'payment_id' => $this->payment->id,
        ];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject("Payment received for {$this->invoice->number}")
            ->success()
            ->line("Amount received: {$this->payment->amount}")
            ->action('Open invoice', url("/admin/invoices/{$this->invoice->id}/edit"));
    }
}
