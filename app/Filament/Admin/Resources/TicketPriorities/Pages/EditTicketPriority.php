<?php

namespace App\Filament\Admin\Resources\TicketPriorities\Pages;

use App\Filament\Admin\Resources\TicketPriorities\TicketPriorityResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditTicketPriority extends EditRecord
{
    protected static string $resource = TicketPriorityResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
