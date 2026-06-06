<?php

namespace App\Filament\Admin\Resources\Proposals\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class ProposalForm
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
                    ->relationship('client', 'id'),
                Select::make('lead_id')
                    ->relationship('lead', 'name'),
                Textarea::make('content')
                    ->required()
                    ->columnSpanFull(),
                TextInput::make('total')
                    ->required()
                    ->numeric()
                    ->default(0.0),
                Select::make('currency_id')
                    ->relationship('currency', 'name')
                    ->required(),
                DatePicker::make('open_until'),
                TextInput::make('status')
                    ->required()
                    ->default('draft'),
                DateTimePicker::make('accepted_at'),
                TextInput::make('accepted_by_name'),
                Textarea::make('accepted_signature')
                    ->columnSpanFull(),
                TextInput::make('accepted_ip'),
                DateTimePicker::make('declined_at'),
                Textarea::make('decline_reason')
                    ->columnSpanFull(),
                TextInput::make('created_by')
                    ->numeric(),
            ]);
    }
}
