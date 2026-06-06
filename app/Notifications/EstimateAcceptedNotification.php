<?php

namespace App\Notifications;

use App\Models\Estimate;
use App\Notifications\Concerns\RespectsPreferences;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class EstimateAcceptedNotification extends Notification
{
    use Queueable, RespectsPreferences;

    public function __construct(public Estimate $estimate) {}

    public function eventKey(): string
    {
        return 'estimate.accepted';
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'title' => "Estimate {$this->estimate->number} accepted",
            'body' => "Total: {$this->estimate->total}",
            'url' => "/admin/estimates/{$this->estimate->id}/edit",
            'icon' => 'heroicon-o-check-badge',
            'estimate_id' => $this->estimate->id,
        ];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject("Estimate {$this->estimate->number} accepted")
            ->success()
            ->line("Your client accepted estimate {$this->estimate->number}.")
            ->action('Open estimate', url("/admin/estimates/{$this->estimate->id}/edit"));
    }
}
