<?php

namespace App\Notifications;

use App\Models\Task;
use App\Notifications\Concerns\RespectsPreferences;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TaskAssignedNotification extends Notification
{
    use Queueable, RespectsPreferences;

    public function __construct(public Task $task) {}

    public function eventKey(): string
    {
        return 'task.assigned';
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'title' => 'New task assigned',
            'body' => $this->task->title,
            'url' => "/admin/tasks/{$this->task->id}/edit",
            'icon' => 'heroicon-o-clipboard-document-list',
            'task_id' => $this->task->id,
        ];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject("Task assigned: {$this->task->title}")
            ->line('You have been assigned a new task.')
            ->action('Open task', url("/admin/tasks/{$this->task->id}/edit"));
    }
}
