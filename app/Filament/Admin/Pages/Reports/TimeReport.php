<?php

namespace App\Filament\Admin\Pages\Reports;

use App\Models\TimeEntry;
use App\Models\User;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;

class TimeReport extends Page
{
    protected string $view = 'filament.admin.pages.reports.time';

    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedClock;

    protected static ?string $navigationLabel = 'Time Report';

    protected static string|\UnitEnum|null $navigationGroup = 'Laporan';

    protected static ?int $navigationSort = 3;

    public string $period = '30_days';

    public function getStats(): array
    {
        $from = match ($this->period) {
            '7_days' => now()->subDays(7),
            '30_days' => now()->subDays(30),
            '90_days' => now()->subDays(90),
            'ytd' => now()->startOfYear(),
            default => now()->subDays(30),
        };

        $entries = TimeEntry::where('start_at', '>=', $from)->whereNotNull('end_at');
        $totalMinutes = (int) (clone $entries)->sum('minutes');
        $billableMinutes = (int) (clone $entries)->where('is_billable', true)->sum('minutes');
        $invoicedMinutes = (int) (clone $entries)->where('is_invoiced', true)->sum('minutes');

        return [
            'total_hours' => round($totalMinutes / 60, 1),
            'billable_hours' => round($billableMinutes / 60, 1),
            'invoiced_hours' => round($invoicedMinutes / 60, 1),
            'unbilled_hours' => round(($billableMinutes - $invoicedMinutes) / 60, 1),
        ];
    }

    public function getByUser(): array
    {
        $from = now()->subDays(30);

        return TimeEntry::query()
            ->selectRaw('user_id, SUM(minutes) as total_minutes, SUM(CASE WHEN is_billable THEN minutes ELSE 0 END) as billable_minutes')
            ->where('start_at', '>=', $from)
            ->whereNotNull('end_at')
            ->groupBy('user_id')
            ->with('user:id,name')
            ->get()
            ->map(fn ($r) => [
                'name' => $r->user?->name ?? '—',
                'total_hours' => round($r->total_minutes / 60, 1),
                'billable_hours' => round($r->billable_minutes / 60, 1),
            ])
            ->sortByDesc('total_hours')
            ->values()
            ->all();
    }
}
