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
            Stat::make('Revenue (this month)', number_format($monthRevenue, 0, ',', '.'))
                ->description(now()->format('M Y'))
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('success')
                ->chart($this->dailySeries(Invoice::query()->where('status', 'paid'), 'invoice_date', 'total', 14)),

            Stat::make('Outstanding balance', number_format($outstanding, 0, ',', '.'))
                ->description('Sent / partial / overdue')
                ->descriptionIcon('heroicon-m-clock')
                ->color($outstanding > 0 ? 'warning' : 'success')
                ->chart($this->weeklySeries(Invoice::query()->whereIn('status', ['sent', 'partial', 'overdue']), 'balance_due', 8)),

            Stat::make('Open tickets', (string) $openTickets)
                ->description('Not resolved or closed')
                ->descriptionIcon('heroicon-m-ticket')
                ->color($openTickets > 0 ? 'warning' : 'success')
                ->chart($this->weeklySeries(Ticket::query(), null, 8, count: true)),

            Stat::make('New leads (this week)', (string) $newLeadsThisWeek)
                ->description($hotLeads ? 'Top lead: ' . number_format((float) $hotLeads, 0, ',', '.') : 'Pipeline empty')
                ->descriptionIcon('heroicon-m-user-plus')
                ->color('info')
                ->chart($this->dailySeries(Lead::query(), 'created_at', null, 7, count: true)),
        ];
    }

    /**
     * Series harian (N hari terakhir) via satu query GROUP BY.
     */
    protected function dailySeries($query, string $dateColumn, ?string $sumColumn, int $days, bool $count = false): array
    {
        $start = now()->subDays($days - 1)->startOfDay();

        $rows = (clone $query)
            ->where($dateColumn, '>=', $start)
            ->selectRaw("DATE({$dateColumn}) as d, " . ($count ? 'COUNT(*)' : "SUM({$sumColumn})") . ' as v')
            ->groupBy('d')
            ->pluck('v', 'd');

        $series = [];
        for ($i = $days - 1; $i >= 0; $i--) {
            $key = now()->subDays($i)->toDateString();
            $series[] = (float) ($rows[$key] ?? 0);
        }

        return $series;
    }

    /**
     * Series mingguan (N minggu terakhir) via satu query GROUP BY.
     */
    protected function weeklySeries($query, ?string $sumColumn, int $weeks, bool $count = false): array
    {
        $start = now()->subWeeks($weeks - 1)->startOfWeek();

        $rows = (clone $query)
            ->where('created_at', '>=', $start)
            ->selectRaw('YEARWEEK(created_at, 3) as wk, ' . ($count ? 'COUNT(*)' : "SUM({$sumColumn})") . ' as v')
            ->groupBy('wk')
            ->pluck('v', 'wk');

        $series = [];
        for ($i = $weeks - 1; $i >= 0; $i--) {
            $key = (int) now()->subWeeks($i)->format('oW');
            $series[] = (float) ($rows[$key] ?? 0);
        }

        return $series;
    }
}
