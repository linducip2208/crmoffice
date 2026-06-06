<?php

namespace App\Filament\Admin\Resources\Projects\Pages;

use App\Actions\Sales\InvoiceTimeEntries;
use App\Filament\Admin\Actions\MeetingNotesAction;
use App\Filament\Admin\Resources\Projects\ProjectResource;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditProject extends EditRecord
{
    protected static string $resource = ProjectResource::class;

    protected function getHeaderActions(): array
    {
        return [
            MeetingNotesAction::make('meetingNotes')
                ->relatedType('project')
                ->relatedId($this->record->id),

            Action::make('invoiceTime')
                ->label('Invoice Tracked Time')
                ->icon('heroicon-o-clock')
                ->color('success')
                ->requiresConfirmation()
                ->modalDescription('Generate a draft invoice from all billable, uninvoiced time entries on this project.')
                ->action(function () {
                    try {
                        $invoice = app(InvoiceTimeEntries::class)->handle($this->record);
                        Notification::make()
                            ->title('Invoice generated')
                            ->body("Invoice {$invoice->number} created.")
                            ->success()
                            ->send();

                        return redirect('/admin/invoices/' . $invoice->id . '/edit');
                    } catch (\Throwable $e) {
                        Notification::make()->title('No billable time')->body($e->getMessage())->warning()->send();
                    }
                }),

            DeleteAction::make(),
            ForceDeleteAction::make(),
            RestoreAction::make(),
        ];
    }
}
