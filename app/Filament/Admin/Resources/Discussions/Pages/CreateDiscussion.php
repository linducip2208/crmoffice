<?php

namespace App\Filament\Admin\Resources\Discussions\Pages;

use App\Filament\Admin\Resources\Discussions\DiscussionResource;
use Filament\Resources\Pages\CreateRecord;

class CreateDiscussion extends CreateRecord
{
    protected static string $resource = DiscussionResource::class;
}
