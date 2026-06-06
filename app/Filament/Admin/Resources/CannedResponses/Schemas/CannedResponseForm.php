<?php

namespace App\Filament\Admin\Resources\CannedResponses\Schemas;

use App\Models\Department;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class CannedResponseForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('title')
                    ->label(__('crm.fields.title'))
                    ->required()
                    ->maxLength(200),
                Textarea::make('content')
                    ->label(__('crm.fields.content'))
                    ->required()
                    ->rows(8)
                    ->columnSpanFull(),
                TextInput::make('category')
                    ->label(__('crm.fields.category'))
                    ->maxLength(100),
                Select::make('department_id')
                    ->label(__('crm.fields.department_id'))
                    ->options(fn () => Department::where('is_active', true)->pluck('name', 'id'))
                    ->searchable()
                    ->placeholder('All departments'),
                Toggle::make('is_shared')
                    ->label(__('crm.fields.is_shared'))
                    ->helperText('Visible to all support staff'),
            ]);
    }
}
