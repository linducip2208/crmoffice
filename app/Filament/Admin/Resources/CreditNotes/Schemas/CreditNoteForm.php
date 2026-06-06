<?php

namespace App\Filament\Admin\Resources\CreditNotes\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class CreditNoteForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('number')
                    ->required(),
                Select::make('client_id')
                    ->relationship('client', 'id')
                    ->required(),
                DatePicker::make('issue_date')
                    ->required(),
                TextInput::make('total')
                    ->required()
                    ->numeric(),
                TextInput::make('applied_total')
                    ->required()
                    ->numeric()
                    ->default(0.0),
                TextInput::make('refunded_total')
                    ->required()
                    ->numeric()
                    ->default(0.0),
                Select::make('currency_id')
                    ->relationship('currency', 'name')
                    ->required(),
                TextInput::make('status')
                    ->required()
                    ->default('open'),
                Textarea::make('reason')
                    ->columnSpanFull(),
                TextInput::make('created_by')
                    ->numeric(),
            ]);
    }
}
