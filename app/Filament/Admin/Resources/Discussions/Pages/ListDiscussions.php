<?php

namespace App\Filament\Admin\Resources\Discussions\Pages;

use App\Filament\Admin\Resources\Discussions\DiscussionResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListDiscussions extends ListRecords
{
    protected static string $resource = DiscussionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
