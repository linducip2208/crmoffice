<?php

namespace App\Filament\Admin\Resources\TicketStatuses\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class TicketStatusForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required(),
                Toggle::make('is_open')
                    ->required(),
                Toggle::make('is_resolved')
                    ->required(),
                TextInput::make('order')
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('color')
                    ->required()
                    ->default('#3b82f6'),
            ]);
    }
}
