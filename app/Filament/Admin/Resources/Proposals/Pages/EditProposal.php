<?php

namespace App\Filament\Admin\Resources\Proposals\Pages;

use App\Filament\Admin\Resources\Proposals\Actions\DraftWithAi;
use App\Filament\Admin\Resources\Proposals\ProposalResource;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditProposal extends EditRecord
{
    protected static string $resource = ProposalResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DraftWithAi::make(),
            Action::make('saveAsTemplate')
                ->label('Save as Template')
                ->icon('heroicon-o-bookmark')
                ->color('warning')
                ->requiresConfirmation()
                ->modalDescription('Mark this proposal as a template for reuse. The current content will be preserved.')
                ->action(function () {
                    $this->record->update(['is_template' => true]);

                    Notification::make()
                        ->title('Saved as Template')
                        ->body('This proposal is now available as a template.')
                        ->success()
                        ->send();
                }),
            DeleteAction::make(),
            ForceDeleteAction::make(),
            RestoreAction::make(),
        ];
    }
}
