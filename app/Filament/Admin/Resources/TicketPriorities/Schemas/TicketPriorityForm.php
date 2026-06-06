<?php

namespace App\Filament\Admin\Resources\TicketPriorities\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class TicketPriorityForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required(),
                TextInput::make('response_minutes_sla')
                    ->numeric(),
                TextInput::make('resolve_minutes_sla')
                    ->numeric(),
                TextInput::make('color')
                    ->required()
                    ->default('#6b7280'),
                TextInput::make('order')
                    ->required()
                    ->numeric()
                    ->default(0),
                Toggle::make('is_active')
                    ->required(),
            ]);
    }
}
