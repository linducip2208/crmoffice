<?php

namespace App\Filament\Admin\Pages;

use App\Models\Lead;
use App\Models\LeadStatus;
use App\Models\User;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Illuminate\Contracts\Support\Htmlable;

class LeadsKanban extends Page
{
    protected string $view = 'filament.admin.pages.leads-kanban';

    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedViewColumns;

    protected static ?string $navigationLabel = 'Kanban';

    protected static string|\UnitEnum|null $navigationGroup = 'CRM';

    protected static ?int $navigationSort = 2;

    protected static ?string $title = 'Leads Kanban';

    protected static ?string $slug = 'leads-kanban';

    public ?int $filterUser = null;

    public function getTitle(): string|Htmlable
    {
        return 'Leads Kanban';
    }

    public function getColumns(): array
    {
        $query = Lead::query()->with(['source', 'assignedTo']);
        if ($this->filterUser) {
            $query->where('assigned_to', $this->filterUser);
        }
        $leads = $query->get()->groupBy('lead_status_id');

        return LeadStatus::orderBy('order')->get()->map(function ($status) use ($leads) {
            return [
                'id' => $status->id,
                'name' => $status->name,
                'color' => $status->color,
                'is_won' => $status->is_won,
                'is_lost' => $status->is_lost,
                'leads' => $leads->get($status->id, collect()),
                'count' => $leads->get($status->id, collect())->count(),
                'total_value' => $leads->get($status->id, collect())->sum('estimated_value'),
            ];
        })->all();
    }

    public function getUsers(): array
    {
        return User::where('is_active', true)->pluck('name', 'id')->toArray();
    }

    public function moveLead(int $leadId, int $statusId): void
    {
        $lead = Lead::find($leadId);
        $status = LeadStatus::find($statusId);
        if (! $lead || ! $status) {
            return;
        }

        $lead->update([
            'lead_status_id' => $statusId,
            'last_activity_at' => now(),
        ]);

        $lead->activities()->create([
            'type' => 'status_change',
            'subject' => "Moved to {$status->name}",
            'user_id' => auth()->id(),
            'occurred_at' => now(),
        ]);
    }
}
