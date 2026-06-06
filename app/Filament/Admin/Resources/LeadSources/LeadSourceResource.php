<?php

namespace App\Filament\Admin\Resources\LeadSources;

use App\Filament\Admin\Resources\LeadSources\Pages\CreateLeadSource;
use App\Filament\Admin\Resources\LeadSources\Pages\EditLeadSource;
use App\Filament\Admin\Resources\LeadSources\Pages\ListLeadSources;
use App\Filament\Admin\Resources\LeadSources\Schemas\LeadSourceForm;
use App\Filament\Admin\Resources\LeadSources\Tables\LeadSourcesTable;
use App\Models\LeadSource;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class LeadSourceResource extends Resource
{
    protected static ?string $model = LeadSource::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedGlobeAlt;

    protected static ?string $navigationLabel = 'Lead Sources';

    protected static string|\UnitEnum|null $navigationGroup = 'Master Data';

    protected static ?int $navigationSort = 6;

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return LeadSourceForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return LeadSourcesTable::configure($table);
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
            'index' => ListLeadSources::route('/'),
            'create' => CreateLeadSource::route('/create'),
            'edit' => EditLeadSource::route('/{record}/edit'),
        ];
    }
}
