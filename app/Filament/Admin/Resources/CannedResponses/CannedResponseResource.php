<?php

namespace App\Filament\Admin\Resources\CannedResponses;

use App\Filament\Admin\Resources\CannedResponses\Pages\CreateCannedResponse;
use App\Filament\Admin\Resources\CannedResponses\Pages\EditCannedResponse;
use App\Filament\Admin\Resources\CannedResponses\Pages\ListCannedResponses;
use App\Filament\Admin\Resources\CannedResponses\Schemas\CannedResponseForm;
use App\Filament\Admin\Resources\CannedResponses\Tables\CannedResponsesTable;
use App\Models\CannedResponse;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Filament\Support\Icons\Heroicon;

class CannedResponseResource extends Resource
{
    protected static ?string $model = CannedResponse::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedClipboardDocumentList;

    protected static ?string $navigationLabel = null;

    protected static string|\UnitEnum|null $navigationGroup = null;

    public static function getNavigationLabel(): string
    {
        return __('crm.module.canned_response');
    }

    public static function getNavigationGroup(): ?string
    {
        return __('crm.navigation.support');
    }

    protected static ?int $navigationSort = 45;

    protected static ?string $recordTitleAttribute = 'title';

    public static function form(Schema $schema): Schema
    {
        return CannedResponseForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return CannedResponsesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListCannedResponses::route('/'),
            'create' => CreateCannedResponse::route('/create'),
            'edit' => EditCannedResponse::route('/{record}/edit'),
        ];
    }
}
