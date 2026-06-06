<?php

namespace App\Filament\Admin\Resources\Contacts\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class ContactForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('client_id')
                    ->relationship('client', 'id')
                    ->required(),
                TextInput::make('first_name')
                    ->required(),
                TextInput::make('last_name'),
                TextInput::make('email')
                    ->label('Email address')
                    ->email(),
                TextInput::make('phone')
                    ->tel(),
                TextInput::make('position'),
                Toggle::make('is_primary')
                    ->required(),
                Toggle::make('portal_access')
                    ->required(),
                TextInput::make('password')
                    ->password(),
                DateTimePicker::make('invitation_expires_at'),
                DateTimePicker::make('last_login_at'),
                Toggle::make('receives_invoice_emails')
                    ->required(),
                Toggle::make('receives_ticket_emails')
                    ->required(),
                Toggle::make('receives_project_emails')
                    ->required(),
                TextInput::make('locale')
                    ->required()
                    ->default('en'),
            ]);
    }
}
