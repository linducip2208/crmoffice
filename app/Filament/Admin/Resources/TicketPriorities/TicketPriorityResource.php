<?php

namespace App\Filament\Admin\Resources\TicketPriorities;

use App\Filament\Admin\Resources\TicketPriorities\Pages\CreateTicketPriority;
use App\Filament\Admin\Resources\TicketPriorities\Pages\EditTicketPriority;
use App\Filament\Admin\Resources\TicketPriorities\Pages\ListTicketPriorities;
use App\Filament\Admin\Resources\TicketPriorities\Schemas\TicketPriorityForm;
use App\Filament\Admin\Resources\TicketPriorities\Tables\TicketPrioritiesTable;
use App\Models\TicketPriority;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class TicketPriorityResource extends Resource
{
    protected static ?string $model = TicketPriority::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedExclamationTriangle;

    protected static ?string $navigationLabel = 'Ticket Priorities';

    protected static string|\UnitEnum|null $navigationGroup = 'Master Data';

    protected static ?int $navigationSort = 8;

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return TicketPriorityForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return TicketPrioritiesTable::configure($table);
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
            'index' => ListTicketPriorities::route('/'),
            'create' => CreateTicketPriority::route('/create'),
            'edit' => EditTicketPriority::route('/{record}/edit'),
        ];
    }
}
