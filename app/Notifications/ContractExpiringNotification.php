<?php

namespace App\Notifications;

use App\Models\Contract;
use App\Notifications\Concerns\RespectsPreferences;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ContractExpiringNotification extends Notification
{
    use Queueable, RespectsPreferences;

    public function __construct(public Contract $contract) {}

    public function eventKey(): string
    {
        return 'contract.expiring';
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'title' => 'Contract expiring',
            'body' => $this->contract->subject,
            'url' => "/admin/contracts/{$this->contract->id}/edit",
            'icon' => 'heroicon-o-document-text',
            'contract_id' => $this->contract->id,
        ];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject("Contract expiring: {$this->contract->subject}")
            ->line("Contract {$this->contract->number} is expiring soon.")
            ->line("Client: " . ($this->contract->client?->company_name ?? 'N/A'))
            ->line("End date: " . ($this->contract->end_date?->format('Y-m-d') ?? 'N/A'))
            ->action('View contract', url("/admin/contracts/{$this->contract->id}/edit"));
    }
}
