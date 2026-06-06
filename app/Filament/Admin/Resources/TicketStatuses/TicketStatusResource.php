<?php

namespace App\Filament\Admin\Resources\TicketStatuses;

use App\Filament\Admin\Resources\TicketStatuses\Pages\CreateTicketStatus;
use App\Filament\Admin\Resources\TicketStatuses\Pages\EditTicketStatus;
use App\Filament\Admin\Resources\TicketStatuses\Pages\ListTicketStatuses;
use App\Filament\Admin\Resources\TicketStatuses\Schemas\TicketStatusForm;
use App\Filament\Admin\Resources\TicketStatuses\Tables\TicketStatusesTable;
use App\Models\TicketStatus;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class TicketStatusResource extends Resource
{
    protected static ?string $model = TicketStatus::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCheckCircle;

    protected static ?string $navigationLabel = 'Ticket Statuses';

    protected static string|\UnitEnum|null $navigationGroup = 'Master Data';

    protected static ?int $navigationSort = 9;

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return TicketStatusForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return TicketStatusesTable::configure($table);
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
            'index' => ListTicketStatuses::route('/'),
            'create' => CreateTicketStatus::route('/create'),
            'edit' => EditTicketStatus::route('/{record}/edit'),
        ];
    }
}
