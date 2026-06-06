<?php

namespace App\Filament\Admin\Resources\Payments\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class PaymentForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('invoice_id')
                    ->relationship('invoice', 'id')
                    ->required(),
                TextInput::make('amount')
                    ->required()
                    ->numeric(),
                Select::make('currency_id')
                    ->relationship('currency', 'name')
                    ->required(),
                TextInput::make('method')
                    ->required(),
                Select::make('provider_id')
                    ->relationship('provider', 'name'),
                TextInput::make('transaction_id'),
                DateTimePicker::make('paid_at')
                    ->required(),
                Textarea::make('note')
                    ->columnSpanFull(),
                TextInput::make('status')
                    ->required()
                    ->default('completed'),
                TextInput::make('raw_payload'),
            ]);
    }
}
