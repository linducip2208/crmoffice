<?php

namespace App\Filament\Admin\Pages\Reports;

use App\Models\Expense;
use App\Models\Invoice;
use App\Models\Project;
use App\Models\TimeEntry;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;

class ProjectProfitabilityReport extends Page
{
    protected string $view = 'filament.admin.pages.reports.project-profitability';

    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedScale;

    protected static ?string $navigationLabel = 'Project Profitability';

    protected static string|\UnitEnum|null $navigationGroup = 'Laporan';

    protected static ?int $navigationSort = 5;

    public function getRows(): array
    {
        return Project::query()
            ->whereNotIn('status', ['cancelled'])
            ->with(['client:id,company_name', 'currency:id,code'])
            ->get()
            ->map(function ($p) {
                $revenue = (float) Invoice::where('project_id', $p->id)
                    ->where('status', '!=', 'void')
                    ->sum('total');

                $expenses = (float) Expense::where('project_id', $p->id)->sum('amount');

                $timeCost = (float) TimeEntry::where('project_id', $p->id)
                    ->whereNotNull('end_at')
                    ->get()
                    ->sum(fn ($e) => ($e->minutes / 60) * ((float) ($e->hourly_rate ?? $p->hourly_rate ?? 0)));

                $profit = $revenue - $expenses - $timeCost;
                $margin = $revenue > 0 ? round(($profit / $revenue) * 100, 1) : null;

                return [
                    'id' => $p->id,
                    'name' => $p->name,
                    'client' => $p->client?->company_name,
                    'status' => $p->status,
                    'currency' => $p->currency?->code ?? 'IDR',
                    'revenue' => $revenue,
                    'expenses' => $expenses,
                    'time_cost' => $timeCost,
                    'profit' => $profit,
                    'margin' => $margin,
                ];
            })
            ->sortByDesc('profit')
            ->values()
            ->all();
    }

    public function getTotals(): array
    {
        $rows = $this->getRows();

        return [
            'revenue' => array_sum(array_column($rows, 'revenue')),
            'expenses' => array_sum(array_column($rows, 'expenses')),
            'time_cost' => array_sum(array_column($rows, 'time_cost')),
            'profit' => array_sum(array_column($rows, 'profit')),
        ];
    }
}
