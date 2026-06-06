<?php

namespace App\Filament\Admin\Resources\TaxRates\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class TaxRateForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required(),
                TextInput::make('percentage')
                    ->required()
                    ->numeric(),
                Toggle::make('is_compound')
                    ->required(),
                Toggle::make('is_active')
                    ->required(),
            ]);
    }
}
