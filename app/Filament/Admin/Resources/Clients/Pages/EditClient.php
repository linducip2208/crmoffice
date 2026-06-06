<?php

namespace App\Filament\Admin\Resources\Clients\Pages;

use App\Filament\Admin\Actions\MeetingNotesAction;
use App\Filament\Admin\Resources\Clients\ClientResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Resources\Pages\EditRecord;

class EditClient extends EditRecord
{
    protected static string $resource = ClientResource::class;

    protected function getHeaderActions(): array
    {
        return [
            MeetingNotesAction::make('meetingNotes')
                ->relatedType('client')
                ->relatedId($this->record->id),

            DeleteAction::make(),
            ForceDeleteAction::make(),
            RestoreAction::make(),
        ];
    }
}
