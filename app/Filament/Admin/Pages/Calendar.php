<?php

namespace App\Filament\Admin\Pages;

use App\Models\CalendarEvent;
use App\Models\Invoice;
use App\Models\Task;
use App\Models\Ticket;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;

class Calendar extends Page
{
    protected string $view = 'filament.admin.pages.calendar';

    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedCalendarDays;

    protected static ?string $navigationLabel = 'Calendar';

    protected static string|\UnitEnum|null $navigationGroup = 'Sistem';

    protected static ?int $navigationSort = 4;

    protected static ?string $slug = 'calendar';

    public function getEvents(): array
    {
        $from = now()->startOfMonth();
        $to = now()->endOfMonth()->copy()->addMonth();

        $events = [];

        CalendarEvent::query()
            ->whereBetween('starts_at', [$from, $to])
            ->get()
            ->each(fn ($e) => $events[] = [
                'title' => $e->title,
                'date' => $e->starts_at->toDateString(),
                'color' => $e->color,
                'type' => 'event',
            ]);

        Task::query()
            ->whereBetween('due_date', [$from, $to])
            ->whereNotIn('status', ['done', 'cancelled'])
            ->get()
            ->each(fn ($t) => $events[] = [
                'title' => 'Task: ' . $t->title,
                'date' => $t->due_date->toDateString(),
                'color' => '#f97316',
                'type' => 'task',
                'url' => "/admin/tasks/{$t->id}/edit",
            ]);

        Invoice::query()
            ->whereBetween('due_date', [$from, $to])
            ->whereNotIn('status', ['paid', 'void'])
            ->get()
            ->each(fn ($i) => $events[] = [
                'title' => "Invoice {$i->number} due",
                'date' => $i->due_date->toDateString(),
                'color' => '#ef4444',
                'type' => 'invoice',
                'url' => "/admin/invoices/{$i->id}/edit",
            ]);

        Ticket::query()
            ->whereBetween('first_response_due_at', [$from, $to])
            ->whereNull('first_response_at')
            ->get()
            ->each(fn ($t) => $events[] = [
                'title' => "Ticket {$t->number} SLA",
                'date' => $t->first_response_due_at->toDateString(),
                'color' => '#dc2626',
                'type' => 'ticket',
                'url' => "/admin/tickets/{$t->id}/edit",
            ]);

        return $events;
    }
}
