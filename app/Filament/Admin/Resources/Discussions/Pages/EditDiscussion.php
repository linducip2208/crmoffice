<?php

namespace App\Filament\Admin\Resources\Discussions\Pages;

use App\Filament\Admin\Resources\Discussions\DiscussionResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditDiscussion extends EditRecord
{
    protected static string $resource = DiscussionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
