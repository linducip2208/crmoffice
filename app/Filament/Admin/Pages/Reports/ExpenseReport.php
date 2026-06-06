<?php

namespace App\Filament\Admin\Pages\Reports;

use App\Models\Expense;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;

class ExpenseReport extends Page
{
    protected string $view = 'filament.admin.pages.reports.expense';

    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedReceiptPercent;

    protected static ?string $navigationLabel = 'Expense Report';

    protected static string|\UnitEnum|null $navigationGroup = 'Laporan';

    protected static ?int $navigationSort = 6;

    public string $period = '6_months';

    public function getStats(): array
    {
        $from = match ($this->period) {
            '1_month' => now()->subMonth(),
            '3_months' => now()->subMonths(3),
            '6_months' => now()->subMonths(6),
            '1_year' => now()->subYear(),
            'ytd' => now()->startOfYear(),
            default => now()->subMonths(6),
        };

        $base = Expense::where('expense_date', '>=', $from);

        return [
            'total' => (float) (clone $base)->sum('amount'),
            'billable' => (float) (clone $base)->where('is_billable', true)->sum('amount'),
            'invoiced' => (float) (clone $base)->where('is_invoiced', true)->sum('amount'),
            'unbilled' => (float) (clone $base)->where('is_billable', true)->where('is_invoiced', false)->sum('amount'),
        ];
    }

    public function getByCategory(): array
    {
        $from = now()->subMonths(6);

        return Expense::query()
            ->selectRaw('expense_category_id, SUM(amount) as total, COUNT(*) as count')
            ->where('expense_date', '>=', $from)
            ->groupBy('expense_category_id')
            ->with('category:id,name')
            ->get()
            ->map(fn ($r) => [
                'name' => $r->category?->name ?? 'Uncategorized',
                'count' => (int) $r->count,
                'total' => (float) $r->total,
            ])
            ->sortByDesc('total')
            ->values()
            ->all();
    }

    public function getByProject(): array
    {
        $from = now()->subMonths(6);

        return Expense::query()
            ->selectRaw('project_id, SUM(amount) as total, COUNT(*) as count')
            ->where('expense_date', '>=', $from)
            ->whereNotNull('project_id')
            ->groupBy('project_id')
            ->with('project:id,name')
            ->get()
            ->map(fn ($r) => [
                'name' => $r->project?->name ?? '—',
                'count' => (int) $r->count,
                'total' => (float) $r->total,
            ])
            ->sortByDesc('total')
            ->values()
            ->all();
    }
}
