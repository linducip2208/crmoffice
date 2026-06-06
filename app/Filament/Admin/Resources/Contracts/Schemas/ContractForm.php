<?php

namespace App\Filament\Admin\Resources\Contracts\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class ContractForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('number')
                    ->required(),
                TextInput::make('subject')
                    ->required(),
                Select::make('client_id')
                    ->relationship('client', 'id')
                    ->required(),
                Textarea::make('content')
                    ->required()
                    ->columnSpanFull(),
                DatePicker::make('start_date')
                    ->required(),
                DatePicker::make('end_date'),
                TextInput::make('contract_value')
                    ->numeric(),
                Select::make('currency_id')
                    ->relationship('currency', 'name')
                    ->required(),
                TextInput::make('status')
                    ->required()
                    ->default('draft'),
                DateTimePicker::make('signed_at'),
                TextInput::make('signed_by_name'),
                Textarea::make('signed_signature')
                    ->columnSpanFull(),
                TextInput::make('signed_ip'),
                TextInput::make('notify_expiry_days_before')
                    ->required()
                    ->numeric()
                    ->default(14),
                TextInput::make('created_by')
                    ->numeric(),
            ]);
    }
}
