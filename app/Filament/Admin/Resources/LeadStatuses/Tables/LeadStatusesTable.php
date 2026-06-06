<?php

namespace App\Filament\Admin\Resources\LeadStatuses\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\ColorColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class LeadStatusesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->reorderable('order')
            ->defaultSort('order')
            ->columns([
                TextColumn::make('order')->label('#')->sortable(),
                TextColumn::make('name')->searchable()->extraCellAttributes(['class' => 'font-semibold']),
                ColorColumn::make('color')->label('Color'),
                IconColumn::make('is_won')->boolean()->label('Won'),
                IconColumn::make('is_lost')->boolean()->label('Lost'),
            ])
            ->recordActions([EditAction::make()])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
