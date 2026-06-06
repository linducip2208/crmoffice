<?php

namespace App\Filament\Admin\Widgets;

use App\Models\Ticket;
use App\Models\TicketReply;
use Carbon\Carbon;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class TicketKpiWidget extends BaseWidget
{
    use DashboardWidgetFilter;

    protected static ?int $sort = 5;
    protected ?string $pollingInterval = '60s';

    protected function getStats(): array
    {
        $openTickets = Ticket::query()
            ->whereNull('resolved_at')
            ->whereNull('closed_at')
            ->count();

        $dueToday = Ticket::query()
            ->whereNull('first_response_at')
            ->whereNotNull('first_response_due_at')
            ->whereDate('first_response_due_at', '<=', now()->toDateString())
            ->whereDate('first_response_due_at', '>=', now()->toDateString())
            ->count();

        $breachedSla = Ticket::query()
            ->whereNull('first_response_at')
            ->whereNotNull('first_response_due_at')
            ->where('first_response_due_at', '<', now())
            ->count();

        $avgResponseMinutes = (int) Ticket::query()
            ->join('ticket_replies', 'tickets.id', '=', 'ticket_replies.ticket_id')
            ->whereNotNull('tickets.first_response_at')
            ->selectRaw('AVG(TIMESTAMPDIFF(MINUTE, ticket_replies.created_at, tickets.first_response_at)) as avg_minutes')
            ->value('avg_minutes');

        $hours = $avgResponseMinutes > 0 ? round($avgResponseMinutes / 60, 1) : 0;

        return [
            Stat::make('Open Tickets', (string) $openTickets)
                ->description('Not resolved or closed')
                ->color($openTickets > 0 ? 'warning' : 'success'),

            Stat::make('Due Today', (string) $dueToday)
                ->description('First response due today')
                ->color($dueToday > 0 ? 'warning' : 'success'),

            Stat::make('Breached SLA', (string) $breachedSla)
                ->description('First response overdue')
                ->color($breachedSla > 0 ? 'danger' : 'success'),

            Stat::make('Avg Response Time', $hours . 'h')
                ->description('Avg first response time')
                ->color($hours > 4 ? 'warning' : 'success'),
        ];
    }
}
