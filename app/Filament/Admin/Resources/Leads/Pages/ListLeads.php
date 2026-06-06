<?php

namespace App\Filament\Admin\Resources\Leads\Pages;

use App\Filament\Admin\Actions\ImportLeadsAction;
use App\Filament\Admin\Resources\Leads\LeadResource;
use App\Services\CsvExportService;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListLeads extends ListRecords
{
    protected static string $resource = LeadResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),

            Action::make('viewKanban')
                ->label('Kanban View')
                ->icon('heroicon-o-view-columns')
                ->color('gray')
                ->url('/admin/leads-kanban'),

            ImportLeadsAction::make(),

            Action::make('exportCsv')
                ->label('Export CSV')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('success')
                ->action(function (CsvExportService $csv) {
                    return $csv->download(
                        query: $this->getFilteredSortedTableQuery(),
                        columns: [
                            'Name' => 'name',
                            'Company' => 'company',
                            'Email' => 'email',
                            'Phone' => 'phone',
                            'Source' => 'source.name',
                            'Status' => 'status.name',
                            'Assigned To' => 'assignedTo.name',
                            'Value' => 'estimated_value',
                            'Expected Close' => fn ($r) => $r->expected_close?->format('Y-m-d') ?? '',
                            'Created' => fn ($r) => $r->created_at?->format('Y-m-d H:i:s') ?? '',
                        ],
                        filename: 'leads-' . now()->format('Y-m-d') . '.csv',
                    );
                }),
        ];
    }
}
