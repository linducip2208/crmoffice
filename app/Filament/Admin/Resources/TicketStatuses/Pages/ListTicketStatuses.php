<?php

namespace App\Filament\Admin\Resources\TicketStatuses\Pages;

use App\Filament\Admin\Resources\TicketStatuses\TicketStatusResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListTicketStatuses extends ListRecords
{
    protected static string $resource = TicketStatusResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
