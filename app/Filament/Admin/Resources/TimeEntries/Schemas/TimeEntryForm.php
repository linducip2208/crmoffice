<?php

namespace App\Filament\Admin\Resources\TimeEntries\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class TimeEntryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('task_id')
                    ->relationship('task', 'title'),
                Select::make('project_id')
                    ->relationship('project', 'name'),
                Select::make('user_id')
                    ->relationship('user', 'name')
                    ->required(),
                DateTimePicker::make('start_at')
                    ->required(),
                DateTimePicker::make('end_at'),
                TextInput::make('minutes')
                    ->numeric(),
                TextInput::make('hourly_rate')
                    ->numeric(),
                Toggle::make('is_billable')
                    ->required(),
                Toggle::make('is_invoiced')
                    ->required(),
                Select::make('invoice_item_id')
                    ->relationship('invoiceItem', 'id'),
                Textarea::make('note')
                    ->columnSpanFull(),
            ]);
    }
}
