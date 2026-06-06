<?php

namespace App\Filament\Admin\Resources\KbCategories\Pages;

use App\Filament\Admin\Resources\KbCategories\KbCategoryResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditKbCategory extends EditRecord
{
    protected static string $resource = KbCategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
