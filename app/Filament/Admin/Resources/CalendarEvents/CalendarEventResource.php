<?php

namespace App\Filament\Admin\Resources\CalendarEvents;

use App\Models\CalendarEvent;
use BackedEnum;
use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class CalendarEventResource extends Resource
{
    protected static ?string $model = CalendarEvent::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCalendarDays;

    protected static ?string $navigationLabel = 'Events';

    protected static string|\UnitEnum|null $navigationGroup = 'Sistem';

    protected static ?int $navigationSort = 2;

    protected static ?string $recordTitleAttribute = 'title';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('title')->required()->maxLength(255),
            Textarea::make('description')->rows(3),
            DateTimePicker::make('starts_at')->required()->default(now()),
            DateTimePicker::make('ends_at')->afterOrEqual('starts_at'),
            Toggle::make('all_day'),
            ColorPicker::make('color')->default('#3b82f6'),
            Select::make('user_id')
                ->relationship('user', 'name')
                ->required()
                ->default(fn () => auth()->id())
                ->preload()
                ->searchable(),
            TextInput::make('reminder_minutes_before')
                ->numeric()
                ->suffix('minutes before')
                ->helperText('Leave blank for no reminder.'),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('title')->searchable()->sortable(),
            TextColumn::make('starts_at')->dateTime()->sortable(),
            TextColumn::make('ends_at')->dateTime(),
            IconColumn::make('all_day')->boolean(),
            TextColumn::make('user.name')->label('Owner'),
        ])
            ->defaultSort('starts_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCalendarEvents::route('/'),
            'create' => Pages\CreateCalendarEvent::route('/create'),
            'edit' => Pages\EditCalendarEvent::route('/{record}/edit'),
        ];
    }
}
