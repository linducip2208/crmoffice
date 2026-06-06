<?php

namespace App\Filament\Admin\Resources\LeadStatuses\Pages;

use App\Filament\Admin\Resources\LeadStatuses\LeadStatusResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditLeadStatus extends EditRecord
{
    protected static string $resource = LeadStatusResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
