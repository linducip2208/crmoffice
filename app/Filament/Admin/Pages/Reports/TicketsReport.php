<?php

namespace App\Filament\Admin\Pages\Reports;

use App\Models\Ticket;
use App\Models\User;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;

class TicketsReport extends Page
{
    protected string $view = 'filament.admin.pages.reports.tickets';

    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedTicket;

    protected static ?string $navigationLabel = 'Tickets Report';

    protected static string|\UnitEnum|null $navigationGroup = 'Laporan';

    protected static ?int $navigationSort = 4;

    public function getStats(): array
    {
        $total = Ticket::count();
        $open = Ticket::whereHas('status', fn ($q) => $q->where('is_open', true))->count();
        $resolved = Ticket::whereNotNull('resolved_at')->count();
        $breached = Ticket::whereNull('resolved_at')
            ->where(function ($q) {
                $q->whereNotNull('first_response_due_at')->where('first_response_due_at', '<', now())->whereNull('first_response_at');
            })->count();

        $avgFirstResponseMinutes = (int) Ticket::whereNotNull('first_response_at')
            ->whereRaw('TIMESTAMPDIFF(MINUTE, created_at, first_response_at) > 0')
            ->avg(\DB::raw('TIMESTAMPDIFF(MINUTE, created_at, first_response_at)'));

        $avgResolveHours = (int) Ticket::whereNotNull('resolved_at')
            ->avg(\DB::raw('TIMESTAMPDIFF(HOUR, created_at, resolved_at)'));

        return compact('total', 'open', 'resolved', 'breached', 'avgFirstResponseMinutes', 'avgResolveHours');
    }

    public function getByAgent(): array
    {
        return Ticket::query()
            ->selectRaw('assigned_to, COUNT(*) as total, SUM(CASE WHEN resolved_at IS NOT NULL THEN 1 ELSE 0 END) as resolved')
            ->whereNotNull('assigned_to')
            ->groupBy('assigned_to')
            ->with('assignee:id,name')
            ->get()
            ->map(fn ($r) => [
                'name' => $r->assignee?->name ?? '—',
                'total' => (int) $r->total,
                'resolved' => (int) $r->resolved,
                'pct' => $r->total > 0 ? round(($r->resolved / $r->total) * 100) : 0,
            ])
            ->sortByDesc('total')
            ->values()
            ->all();
    }
}
