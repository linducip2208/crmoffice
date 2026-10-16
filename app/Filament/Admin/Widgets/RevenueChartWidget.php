<?php

namespace App\Filament\Admin\Widgets;

use App\Models\Invoice;
use Filament\Widgets\Widget;

class RevenueChartWidget extends Widget
{
    use DashboardWidgetFilter;

    protected static ?int $sort = 2;
    protected int|string|array $columnSpan = 'full';
    protected string $view = 'filament.admin.widgets.revenue-chart';
    protected static ?string $heading = 'Revenue';

    protected static function isVisibleToRole(array $roles): bool
    {
        return !empty(array_intersect($roles, ['owner', 'admin', 'accountant']));
    }

    protected function getViewData(): array
    {
        // Data 24 bulan sekaligus — pemilihan rentang (3/6/12/24M) dilakukan di client
        // agar perpindahan periode instan tanpa roundtrip ke server.
        $months = [];
        $monthKeys = [];

        for ($i = 23; $i >= 0; $i--) {
            $date = now()->startOfMonth()->subMonths($i);
            $monthKeys[] = $date->format('Y-m');
            $months[] = $date->translatedFormat('M Y');
        }

        $start = now()->startOfMonth()->subMonths(23);

        // Dua query agregat — jauh lebih murah dari loop per bulan.
        $paidByMonth = Invoice::query()
            ->where('status', 'paid')
            ->where('invoice_date', '>=', $start)
            ->selectRaw("DATE_FORMAT(invoice_date, '%Y-%m') as ym, SUM(total) as total")
            ->groupBy('ym')
            ->pluck('total', 'ym');

        $outstandingByMonth = Invoice::query()
            ->whereIn('status', ['sent', 'partial', 'overdue'])
            ->where('invoice_date', '>=', $start)
            ->selectRaw("DATE_FORMAT(invoice_date, '%Y-%m') as ym, SUM(total) as total")
            ->groupBy('ym')
            ->pluck('total', 'ym');

        return [
            'months' => $months,
            'revenue' => array_map(fn ($k) => (int) ($paidByMonth[$k] ?? 0), $monthKeys),
            'outstanding' => array_map(fn ($k) => (int) ($outstandingByMonth[$k] ?? 0), $monthKeys),
        ];
    }
}
