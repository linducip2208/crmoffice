<?php

namespace App\Filament\Admin\Resources\Clients\Pages;

use App\Filament\Admin\Actions\ImportClientsAction;
use App\Filament\Admin\Resources\Clients\ClientResource;
use App\Services\CsvExportService;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListClients extends ListRecords
{
    protected static string $resource = ClientResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),

            ImportClientsAction::make(),

            Action::make('exportCsv')
                ->label('Export CSV')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('success')
                ->action(function (CsvExportService $csv) {
                    return $csv->download(
                        query: $this->getFilteredSortedTableQuery(),
                        columns: [
                            'Company' => 'company_name',
                            'Industry' => 'industry',
                            'Website' => 'website',
                            'Phone' => 'phone',
                            'Address' => 'billing_address',
                            'City' => 'billing_city',
                            'State' => 'billing_state',
                            'Postal Code' => 'billing_postal',
                            'Country' => 'billing_country',
                            'Tax ID' => 'tax_id',
                            'Status' => 'status',
                            'Manager' => 'accountManager.name',
                            'Contacts' => fn ($r) => $r->contacts_count ?? $r->contacts()->count(),
                            'Created' => fn ($r) => $r->created_at?->format('Y-m-d H:i:s') ?? '',
                        ],
                        filename: 'clients-' . now()->format('Y-m-d') . '.csv',
                    );
                }),
        ];
    }
}
