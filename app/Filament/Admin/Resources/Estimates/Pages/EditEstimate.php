<?php

namespace App\Filament\Admin\Resources\Estimates\Pages;

use App\Actions\Sales\ConvertEstimateToInvoice;
use App\Filament\Admin\Resources\Estimates\EstimateResource;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditEstimate extends EditRecord
{
    protected static string $resource = EstimateResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('convertToInvoice')
                ->label('Convert to Invoice')
                ->icon('heroicon-o-arrow-right-circle')
                ->color('success')
                ->visible(fn () => ! $this->record->converted_invoice_id)
                ->requiresConfirmation()
                ->modalDescription('Create an Invoice from this Estimate (copying line items). The estimate will be marked converted.')
                ->action(function () {
                    $invoice = app(ConvertEstimateToInvoice::class)->handle($this->record);

                    Notification::make()
                        ->title('Estimate converted')
                        ->body("Invoice {$invoice->number} created.")
                        ->success()
                        ->send();

                    return redirect('/admin/invoices/' . $invoice->id . '/edit');
                }),

            Action::make('viewInvoice')
                ->label('View Linked Invoice')
                ->icon('heroicon-o-document-currency-dollar')
                ->color('info')
                ->visible(fn () => (bool) $this->record->converted_invoice_id)
                ->url(fn () => '/admin/invoices/' . $this->record->converted_invoice_id . '/edit'),

            DeleteAction::make(),
            ForceDeleteAction::make(),
            RestoreAction::make(),
        ];
    }
}
