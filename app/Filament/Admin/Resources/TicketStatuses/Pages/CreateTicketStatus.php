<?php

namespace App\Filament\Admin\Resources\TicketStatuses\Pages;

use App\Filament\Admin\Resources\TicketStatuses\TicketStatusResource;
use Filament\Resources\Pages\CreateRecord;

class CreateTicketStatus extends CreateRecord
{
    protected static string $resource = TicketStatusResource::class;
}
