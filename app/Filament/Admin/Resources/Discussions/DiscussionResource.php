<?php

namespace App\Filament\Admin\Resources\Discussions;

use App\Filament\Admin\Resources\Discussions\Pages\CreateDiscussion;
use App\Filament\Admin\Resources\Discussions\Pages\EditDiscussion;
use App\Filament\Admin\Resources\Discussions\Pages\ListDiscussions;
use App\Filament\Admin\Resources\Discussions\Schemas\DiscussionForm;
use App\Filament\Admin\Resources\Discussions\Tables\DiscussionsTable;
use App\Models\Discussion;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class DiscussionResource extends Resource
{
    protected static ?string $model = Discussion::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedChatBubbleLeftRight;

    protected static ?string $navigationLabel = 'Discussions';

    protected static string|\UnitEnum|null $navigationGroup = 'Proyek';

    protected static ?int $navigationSort = 5;

    protected static ?string $recordTitleAttribute = 'subject';

    public static function form(Schema $schema): Schema
    {
        return DiscussionForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return DiscussionsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            \App\Filament\Admin\Resources\Discussions\RelationManagers\RepliesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListDiscussions::route('/'),
            'create' => CreateDiscussion::route('/create'),
            'edit' => EditDiscussion::route('/{record}/edit'),
        ];
    }
}
