<?php

namespace App\Filament\Admin\Pages\Reports;

use App\Models\Invoice;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;

class AgingReport extends Page
{
    protected string $view = 'filament.admin.pages.reports.aging-report';

    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedClock;

    protected static ?string $navigationLabel = 'Invoice Aging';

    protected static string|\UnitEnum|null $navigationGroup = 'Laporan';

    protected static ?int $navigationSort = 7;

    public function getAgingBuckets(): array
    {
        $now = now();
        $outstanding = Invoice::whereIn('status', ['sent', 'partial', 'overdue'])
            ->where('balance_due', '>', 0)
            ->get();

        $notDue = $outstanding->filter(fn ($i) => $i->due_date && $i->due_date->gte($now));
        $days1_30 = $outstanding->filter(fn ($i) => $i->due_date && $i->due_date->lt($now) && $i->due_date->gte($now->copy()->subDays(30)));
        $days31_60 = $outstanding->filter(fn ($i) => $i->due_date && $i->due_date->lt($now->copy()->subDays(30)) && $i->due_date->gte($now->copy()->subDays(60)));
        $days61_90 = $outstanding->filter(fn ($i) => $i->due_date && $i->due_date->lt($now->copy()->subDays(60)) && $i->due_date->gte($now->copy()->subDays(90)));
        $days90Plus = $outstanding->filter(fn ($i) => $i->due_date && $i->due_date->lt($now->copy()->subDays(90)));

        return [
            'not_due' => [
                'label' => 'Not Due',
                'count' => $notDue->count(),
                'total' => (float) $notDue->sum('balance_due'),
            ],
            '1_30' => [
                'label' => '1-30 Days',
                'count' => $days1_30->count(),
                'total' => (float) $days1_30->sum('balance_due'),
            ],
            '31_60' => [
                'label' => '31-60 Days',
                'count' => $days31_60->count(),
                'total' => (float) $days31_60->sum('balance_due'),
            ],
            '61_90' => [
                'label' => '61-90 Days',
                'count' => $days61_90->count(),
                'total' => (float) $days61_90->sum('balance_due'),
            ],
            '90_plus' => [
                'label' => '>90 Days',
                'count' => $days90Plus->count(),
                'total' => (float) $days90Plus->sum('balance_due'),
            ],
        ];
    }

    public function getTotalOutstanding(): array
    {
        $outstanding = Invoice::whereIn('status', ['sent', 'partial', 'overdue'])
            ->where('balance_due', '>', 0);

        return [
            'count' => $outstanding->count(),
            'total' => (float) $outstanding->sum('balance_due'),
        ];
    }

    public function getTopClientsAging(): array
    {
        $now = now();

        $top = Invoice::query()
            ->selectRaw('client_id, SUM(balance_due) as total_balance')
            ->whereIn('status', ['sent', 'partial', 'overdue'])
            ->where('balance_due', '>', 0)
            ->groupBy('client_id')
            ->orderByDesc('total_balance')
            ->limit(10)
            ->with('client:id,company_name')
            ->get();

        return $top->map(function ($row) use ($now) {
            $clientInvoices = Invoice::where('client_id', $row->client_id)
                ->whereIn('status', ['sent', 'partial', 'overdue'])
                ->where('balance_due', '>', 0)
                ->get();

            return [
                'name' => $row->client?->company_name ?? '—',
                'total' => (float) $row->total_balance,
                'count' => $clientInvoices->count(),
                'not_due' => (float) $clientInvoices->filter(fn ($i) => $i->due_date && $i->due_date->gte($now))->sum('balance_due'),
                '1_30' => (float) $clientInvoices->filter(fn ($i) => $i->due_date && $i->due_date->lt($now) && $i->due_date->gte($now->copy()->subDays(30)))->sum('balance_due'),
                '31_60' => (float) $clientInvoices->filter(fn ($i) => $i->due_date && $i->due_date->lt($now->copy()->subDays(30)) && $i->due_date->gte($now->copy()->subDays(60)))->sum('balance_due'),
                '61_90' => (float) $clientInvoices->filter(fn ($i) => $i->due_date && $i->due_date->lt($now->copy()->subDays(60)) && $i->due_date->gte($now->copy()->subDays(90)))->sum('balance_due'),
                '90_plus' => (float) $clientInvoices->filter(fn ($i) => $i->due_date && $i->due_date->lt($now->copy()->subDays(90)))->sum('balance_due'),
            ];
        })->all();
    }
}
