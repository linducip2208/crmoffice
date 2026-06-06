<?php

namespace App\Notifications;

use App\Models\CalendarEvent;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\DatabaseMessage;
use Illuminate\Notifications\Notification;

class ReminderNotification extends Notification
{
    use Queueable;

    public function __construct(
        protected CalendarEvent $reminder,
        protected string $entityLabel,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'title' => $this->reminder->title,
            'entity_label' => $this->entityLabel,
            'entity_type' => $this->reminder->related_type,
            'entity_id' => $this->reminder->related_id,
            'remind_at' => $this->reminder->starts_at?->toDateTimeString(),
            'url' => $this->resolveUrl(),
        ];
    }

    public function toArray(object $notifiable): array
    {
        return $this->toDatabase($notifiable);
    }

    protected function resolveUrl(): ?string
    {
        $type = $this->reminder->related_type;
        $id = $this->reminder->related_id;

        return match ($type) {
            \App\Models\Invoice::class => "/admin/invoices/{$id}",
            \App\Models\Lead::class => "/admin/leads/{$id}",
            \App\Models\Project::class => "/admin/projects/{$id}",
            \App\Models\Task::class => "/admin/tasks/{$id}",
            \App\Models\Ticket::class => "/admin/tickets/{$id}",
            default => null,
        };
    }
}
