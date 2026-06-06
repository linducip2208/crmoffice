<?php

namespace App\Filament\Admin\Resources\TicketPriorities\Pages;

use App\Filament\Admin\Resources\TicketPriorities\TicketPriorityResource;
use Filament\Resources\Pages\CreateRecord;

class CreateTicketPriority extends CreateRecord
{
    protected static string $resource = TicketPriorityResource::class;
}
