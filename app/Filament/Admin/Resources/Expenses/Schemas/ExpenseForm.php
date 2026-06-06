<?php

namespace App\Filament\Admin\Resources\Expenses\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class ExpenseForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('expense_category_id')
                    ->numeric(),
                Select::make('client_id')
                    ->relationship('client', 'id'),
                Select::make('project_id')
                    ->relationship('project', 'name'),
                TextInput::make('vendor'),
                Textarea::make('description')
                    ->required()
                    ->columnSpanFull(),
                TextInput::make('amount')
                    ->required()
                    ->numeric(),
                Select::make('currency_id')
                    ->relationship('currency', 'name')
                    ->required(),
                Select::make('tax_rate_id')
                    ->relationship('taxRate', 'name'),
                DatePicker::make('expense_date')
                    ->required(),
                Toggle::make('is_billable')
                    ->required(),
                Toggle::make('is_invoiced')
                    ->required(),
                TextInput::make('invoice_item_id')
                    ->numeric(),
                TextInput::make('receipt_file_id')
                    ->numeric(),
                TextInput::make('created_by')
                    ->numeric(),
            ]);
    }
}
