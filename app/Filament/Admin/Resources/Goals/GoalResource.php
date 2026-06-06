<?php

namespace App\Filament\Admin\Resources\Goals;

use App\Models\Goal;
use BackedEnum;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class GoalResource extends Resource
{
    protected static ?string $model = Goal::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedTrophy;

    protected static ?string $navigationLabel = 'Goals';

    protected static string|\UnitEnum|null $navigationGroup = 'Sistem';

    protected static ?int $navigationSort = 3;

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('name')->required()->maxLength(180),
            Textarea::make('description')->rows(3),
            Select::make('metric')
                ->options([
                    'revenue' => 'Revenue',
                    'invoices_paid_count' => 'Invoices paid (count)',
                    'tickets_resolved' => 'Tickets resolved',
                    'leads_converted' => 'Leads converted',
                ])
                ->required(),
            TextInput::make('target')->numeric()->required(),
            TextInput::make('current')->numeric()->default(0),
            DatePicker::make('start_date')->required(),
            DatePicker::make('end_date')->required()->afterOrEqual('start_date'),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('name')->searchable()->sortable(),
            TextColumn::make('metric')->badge(),
            TextColumn::make('current'),
            TextColumn::make('target'),
            TextColumn::make('end_date')->date(),
        ])
            ->defaultSort('end_date', 'asc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListGoals::route('/'),
            'create' => Pages\CreateGoal::route('/create'),
            'edit' => Pages\EditGoal::route('/{record}/edit'),
        ];
    }
}
