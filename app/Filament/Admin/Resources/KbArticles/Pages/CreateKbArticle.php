<?php

namespace App\Filament\Admin\Resources\KbArticles\Pages;

use App\Filament\Admin\Resources\KbArticles\KbArticleResource;
use Filament\Resources\Pages\CreateRecord;

class CreateKbArticle extends CreateRecord
{
    protected static string $resource = KbArticleResource::class;
}
