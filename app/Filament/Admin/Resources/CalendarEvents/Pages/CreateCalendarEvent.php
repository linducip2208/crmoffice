<?php

namespace App\Filament\Admin\Resources\CalendarEvents\Pages;

use App\Filament\Admin\Resources\CalendarEvents\CalendarEventResource;
use Filament\Resources\Pages\CreateRecord;

class CreateCalendarEvent extends CreateRecord
{
    protected static string $resource = CalendarEventResource::class;
}
