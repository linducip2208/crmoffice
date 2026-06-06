<?php

namespace App\Services;

use App\Models\CalendarEvent;
use App\Models\User;
use App\Models\Invoice;
use App\Models\Lead;
use App\Models\Project;
use App\Models\Task;
use App\Models\Ticket;
use Illuminate\Support\Carbon;

class ReminderService
{
    public function createReminder(
        string $entityType,
        int $entityId,
        int $userId,
        Carbon $remindAt,
        string $message
    ): CalendarEvent {
        return CalendarEvent::create([
            'user_id' => $userId,
            'title' => $message,
            'starts_at' => $remindAt,
            'all_day' => false,
            'color' => '#f59e0b',
            'related_type' => $entityType,
            'related_id' => $entityId,
        ]);
    }

    public function attachToEntity($entity, Carbon $remindAt, string $message): CalendarEvent
    {
        $userId = method_exists($entity, 'assignedTo')
            ? ($entity->assignedTo?->id ?? $entity->created_by ?? 1)
            : ($entity->created_by ?? 1);

        return $this->createReminder(
            get_class($entity),
            $entity->id,
            $userId,
            $remindAt,
            $message
        );
    }

    public function sendDueReminders(): void
    {
        $dueReminders = CalendarEvent::query()
            ->whereNotNull('related_type')
            ->whereNotNull('related_id')
            ->whereNull('reminder_sent_at')
            ->where('starts_at', '<=', now())
            ->get();

        foreach ($dueReminders as $reminder) {
            $entity = $this->resolveEntity($reminder->related_type, $reminder->related_id);

            if ($entity) {
                $this->notifyUser($reminder->user, $reminder, $entity);
            }

            $reminder->updateQuietly(['reminder_sent_at' => now()]);
        }
    }

    protected function resolveEntity(string $type, int $id): mixed
    {
        $map = [
            Invoice::class,
            Lead::class,
            Project::class,
            Task::class,
            Ticket::class,
        ];

        foreach ($map as $class) {
            if ($type === $class) {
                return $class::find($id);
            }
        }

        return null;
    }

    protected function notifyUser(User $user, CalendarEvent $reminder, mixed $entity): void
    {
        $entityLabel = $this->entityLabel($entity);

        $user->notify(new \App\Notifications\ReminderNotification(
            $reminder,
            $entityLabel,
        ));
    }

    protected function entityLabel(mixed $entity): string
    {
        if ($entity instanceof Invoice) {
            return "Invoice #{$entity->number}";
        }
        if ($entity instanceof Lead) {
            return "Lead: {$entity->name}";
        }
        if ($entity instanceof Project) {
            return "Project: {$entity->name}";
        }
        if ($entity instanceof Task) {
            return "Task: {$entity->title}";
        }
        if ($entity instanceof Ticket) {
            return "Ticket #{$entity->number}";
        }

        return 'Unknown';
    }
}
