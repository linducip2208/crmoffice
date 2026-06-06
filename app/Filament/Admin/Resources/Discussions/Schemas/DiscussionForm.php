<?php

namespace App\Filament\Admin\Resources\Discussions\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class DiscussionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('project_id')
                    ->relationship('project', 'name')
                    ->required(),
                TextInput::make('subject')
                    ->required(),
                Textarea::make('body')
                    ->columnSpanFull(),
                Select::make('user_id')
                    ->relationship('user', 'name'),
                Toggle::make('is_visible_to_customer')
                    ->required(),
            ]);
    }
}
