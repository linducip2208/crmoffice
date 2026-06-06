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
    protected static ?string $heading = 'Revenue (12 months)';

    protected static function isVisibleToRole(array $roles): bool
    {
        return !empty(array_intersect($roles, ['owner', 'admin', 'accountant']));
    }

    protected function getViewData(): array
    {
        $months = [];
        $revenue = [];
        $outstanding = [];

        for ($i = 11; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $months[] = $date->format('M Y');

            $revenue[] = (int) Invoice::query()
                ->where('status', 'paid')
                ->whereMonth('invoice_date', $date->month)
                ->whereYear('invoice_date', $date->year)
                ->sum('total');

            $outstanding[] = (int) Invoice::query()
                ->whereIn('status', ['sent', 'partial', 'overdue'])
                ->whereMonth('invoice_date', $date->month)
                ->whereYear('invoice_date', $date->year)
                ->sum('total');
        }

        return [
            'months' => $months,
            'revenue' => $revenue,
            'outstanding' => $outstanding,
        ];
    }

}
