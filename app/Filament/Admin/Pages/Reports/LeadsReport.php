<?php

namespace App\Filament\Admin\Pages\Reports;

use App\Models\Lead;
use App\Models\LeadStatus;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;

class LeadsReport extends Page
{
    protected string $view = 'filament.admin.pages.reports.leads';

    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedSparkles;

    protected static ?string $navigationLabel = 'Leads Report';

    protected static string|\UnitEnum|null $navigationGroup = 'Laporan';

    protected static ?int $navigationSort = 2;

    public function getStats(): array
    {
        $now = now();
        $monthAgo = $now->copy()->subMonth();

        $total = Lead::count();
        $thisMonth = Lead::where('created_at', '>=', $monthAgo)->count();
        $converted = Lead::whereNotNull('converted_to_client_id')->count();
        $conversionRate = $total > 0 ? round(($converted / $total) * 100, 1) : 0;
        $totalPipelineValue = (float) Lead::whereNull('converted_to_client_id')
            ->whereHas('status', fn ($q) => $q->where('is_lost', false))
            ->sum('estimated_value');

        return compact('total', 'thisMonth', 'converted', 'conversionRate', 'totalPipelineValue');
    }

    public function getByStatus(): array
    {
        return LeadStatus::orderBy('order')
            ->get()
            ->map(fn ($s) => [
                'name' => $s->name,
                'color' => $s->color,
                'count' => Lead::where('lead_status_id', $s->id)->count(),
                'value' => (float) Lead::where('lead_status_id', $s->id)->sum('estimated_value'),
            ])
            ->all();
    }

    public function getBySource(): array
    {
        return Lead::query()
            ->selectRaw('lead_source_id, COUNT(*) as count, SUM(estimated_value) as value')
            ->groupBy('lead_source_id')
            ->with('source:id,name')
            ->get()
            ->map(fn ($r) => [
                'name' => $r->source?->name ?? 'Unknown',
                'count' => (int) $r->count,
                'value' => (float) $r->value,
            ])
            ->sortByDesc('count')
            ->values()
            ->all();
    }
}
