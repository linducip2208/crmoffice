<?php

namespace App\Filament\Admin\Resources\Items\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class ItemForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required(),
                Textarea::make('description')
                    ->columnSpanFull(),
                TextInput::make('default_price')
                    ->required()
                    ->numeric()
                    ->default(0.0)
                    ->prefix('$'),
                Select::make('default_tax_rate_id')
                    ->relationship('defaultTaxRate', 'name'),
                Select::make('currency_id')
                    ->relationship('currency', 'name'),
                TextInput::make('unit'),
                TextInput::make('sku')
                    ->label('SKU'),
                Toggle::make('is_active')
                    ->required(),
            ]);
    }
}
