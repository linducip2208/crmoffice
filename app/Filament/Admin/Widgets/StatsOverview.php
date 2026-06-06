<?php

namespace App\Filament\Admin\Widgets;

use App\Models\Invoice;
use App\Models\Lead;
use App\Models\Ticket;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverview extends BaseWidget
{
    use DashboardWidgetFilter;

    protected static ?int $sort = 1;
    protected ?string $pollingInterval = '60s';

    protected function getStats(): array
    {
        $monthRevenue = Invoice::query()
            ->where('status', 'paid')
            ->whereMonth('invoice_date', now()->month)
            ->whereYear('invoice_date', now()->year)
            ->sum('total');

        $outstanding = Invoice::query()
            ->whereIn('status', ['sent', 'partial', 'overdue'])
            ->sum('balance_due');

        $openTickets = Ticket::query()->whereNull('resolved_at')->whereNull('closed_at')->count();

        $hotLeads = Lead::query()
            ->whereNull('converted_at')
            ->whereNotNull('estimated_value')
            ->orderByDesc('estimated_value')
            ->limit(1)
            ->value('estimated_value');

        $newLeadsThisWeek = Lead::query()
            ->where('created_at', '>=', now()->startOfWeek())
            ->count();

        return [
            Stat::make('Revenue (this month)', number_format($monthRevenue, 0))
                ->description(now()->format('M Y'))
                ->color('success'),

            Stat::make('Outstanding balance', number_format($outstanding, 0))
                ->description('Across sent/partial/overdue invoices')
                ->color($outstanding > 0 ? 'warning' : 'success'),

            Stat::make('Open tickets', (string) $openTickets)
                ->description('Not resolved or closed')
                ->color($openTickets > 0 ? 'warning' : 'success'),

            Stat::make('New leads (this week)', (string) $newLeadsThisWeek)
                ->description($hotLeads ? "Top lead: ".number_format((float) $hotLeads, 0) : 'Pipeline empty')
                ->color('info'),
        ];
    }
}
