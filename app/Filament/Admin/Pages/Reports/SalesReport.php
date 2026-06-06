<?php

namespace App\Filament\Admin\Pages\Reports;

use App\Models\Invoice;
use App\Models\Payment;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Carbon;

class SalesReport extends Page
{
    protected string $view = 'filament.admin.pages.reports.sales';

    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedChartBar;

    protected static ?string $navigationLabel = 'Sales Report';

    protected static string|\UnitEnum|null $navigationGroup = 'Laporan';

    protected static ?int $navigationSort = 1;

    public string $period = '6_months';

    public function getStats(): array
    {
        $from = match ($this->period) {
            '1_month' => now()->subMonth()->startOfDay(),
            '3_months' => now()->subMonths(3)->startOfDay(),
            '6_months' => now()->subMonths(6)->startOfDay(),
            '1_year' => now()->subYear()->startOfDay(),
            'ytd' => now()->startOfYear(),
            default => now()->subMonths(6)->startOfDay(),
        };

        return [
            'total_invoiced' => (float) Invoice::where('status', '!=', 'void')->where('invoice_date', '>=', $from)->sum('total'),
            'total_paid' => (float) Invoice::where('invoice_date', '>=', $from)->sum('paid_total'),
            'total_outstanding' => (float) Invoice::whereIn('status', ['sent', 'partial', 'overdue'])->where('invoice_date', '>=', $from)->sum('balance_due'),
            'invoice_count' => (int) Invoice::where('status', '!=', 'void')->where('invoice_date', '>=', $from)->count(),
            'paid_count' => (int) Invoice::where('status', 'paid')->where('invoice_date', '>=', $from)->count(),
            'overdue_count' => (int) Invoice::where('status', 'overdue')->count(),
        ];
    }

    public function getMonthlyRevenue(): array
    {
        $months = collect();
        for ($i = 11; $i >= 0; $i--) {
            $monthStart = now()->subMonths($i)->startOfMonth();
            $monthEnd = now()->subMonths($i)->endOfMonth();
            $months->push([
                'label' => $monthStart->format('M Y'),
                'invoiced' => (float) Invoice::where('status', '!=', 'void')->whereBetween('invoice_date', [$monthStart, $monthEnd])->sum('total'),
                'paid' => (float) Payment::whereBetween('paid_at', [$monthStart, $monthEnd])->sum('amount'),
            ]);
        }

        return $months->all();
    }

    public function getTopClients(): array
    {
        return Invoice::query()
            ->selectRaw('client_id, SUM(total) as total, COUNT(*) as invoice_count')
            ->where('status', '!=', 'void')
            ->where('invoice_date', '>=', now()->subYear())
            ->groupBy('client_id')
            ->orderByDesc('total')
            ->limit(10)
            ->with('client:id,company_name')
            ->get()
            ->map(fn ($r) => [
                'name' => $r->client?->company_name ?? '—',
                'total' => (float) $r->total,
                'count' => (int) $r->invoice_count,
            ])
            ->all();
    }
}
