<?php

namespace App\Filament\Admin\Resources\Invoices\Pages;

use App\Filament\Admin\Resources\Invoices\InvoiceResource;
use App\Services\CsvExportService;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListInvoices extends ListRecords
{
    protected static string $resource = InvoiceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),

            Action::make('exportCsv')
                ->label('Export CSV')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('success')
                ->action(function (CsvExportService $csv) {
                    return $csv->download(
                        query: $this->getFilteredSortedTableQuery(),
                        columns: [
                            'Number' => 'number',
                            'Client' => 'client.company_name',
                            'Invoice Date' => fn ($r) => $r->invoice_date?->format('Y-m-d') ?? '',
                            'Due Date' => fn ($r) => $r->due_date?->format('Y-m-d') ?? '',
                            'Currency' => 'currency.code',
                            'Subtotal' => 'subtotal',
                            'Discount' => 'discount_total',
                            'Tax' => 'tax_total',
                            'Total' => 'total',
                            'Paid' => 'paid_total',
                            'Balance Due' => 'balance_due',
                            'Status' => 'status',
                            'Recurring' => fn ($r) => $r->is_recurring ? 'Yes' : 'No',
                            'Created' => fn ($r) => $r->created_at?->format('Y-m-d H:i:s') ?? '',
                        ],
                        filename: 'invoices-' . now()->format('Y-m-d') . '.csv',
                    );
                }),
        ];
    }
}
