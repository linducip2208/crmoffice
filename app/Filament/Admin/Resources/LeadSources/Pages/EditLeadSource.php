<?php

namespace App\Filament\Admin\Resources\LeadSources\Pages;

use App\Filament\Admin\Resources\LeadSources\LeadSourceResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditLeadSource extends EditRecord
{
    protected static string $resource = LeadSourceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
