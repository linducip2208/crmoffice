<?php

namespace App\Filament\Admin\Resources\Contracts\Pages;

use App\Filament\Admin\Resources\Contracts\ContractResource;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditContract extends EditRecord
{
    protected static string $resource = ContractResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('saveAsTemplate')
                ->label('Save as Template')
                ->icon('heroicon-o-bookmark')
                ->color('warning')
                ->requiresConfirmation()
                ->modalDescription('Mark this contract as a template for reuse. The current content will be preserved.')
                ->action(function () {
                    $this->record->update(['is_template' => true]);

                    Notification::make()
                        ->title('Saved as Template')
                        ->body('This contract is now available as a template.')
                        ->success()
                        ->send();
                }),
            DeleteAction::make(),
            ForceDeleteAction::make(),
            RestoreAction::make(),
        ];
    }
}
