<?php

namespace App\Filament\Admin\Resources\Departments\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class DepartmentForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required(),
                TextInput::make('email_pipe')
                    ->email(),
                Textarea::make('description')
                    ->columnSpanFull(),
                Select::make('default_assignee_id')
                    ->relationship('defaultAssignee', 'name'),
                Toggle::make('is_active')
                    ->required(),
            ]);
    }
}
