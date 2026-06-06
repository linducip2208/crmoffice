<?php

namespace App\Filament\Admin\Resources\LeadStatuses;

use App\Filament\Admin\Resources\LeadStatuses\Pages\CreateLeadStatus;
use App\Filament\Admin\Resources\LeadStatuses\Pages\EditLeadStatus;
use App\Filament\Admin\Resources\LeadStatuses\Pages\ListLeadStatuses;
use App\Filament\Admin\Resources\LeadStatuses\Schemas\LeadStatusForm;
use App\Filament\Admin\Resources\LeadStatuses\Tables\LeadStatusesTable;
use App\Models\LeadStatus;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class LeadStatusResource extends Resource
{
    protected static ?string $model = LeadStatus::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedFlag;

    protected static ?string $navigationLabel = 'Lead Statuses';

    protected static string|\UnitEnum|null $navigationGroup = 'Master Data';

    protected static ?int $navigationSort = 7;

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return LeadStatusForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return LeadStatusesTable::configure($table);
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
            'index' => ListLeadStatuses::route('/'),
            'create' => CreateLeadStatus::route('/create'),
            'edit' => EditLeadStatus::route('/{record}/edit'),
        ];
    }
}
