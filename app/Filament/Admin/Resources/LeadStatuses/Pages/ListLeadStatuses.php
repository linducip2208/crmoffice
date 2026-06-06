<?php

namespace App\Filament\Admin\Resources\LeadStatuses\Pages;

use App\Filament\Admin\Resources\LeadStatuses\LeadStatusResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListLeadStatuses extends ListRecords
{
    protected static string $resource = LeadStatusResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
