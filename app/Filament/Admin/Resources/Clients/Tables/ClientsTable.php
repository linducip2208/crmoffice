<?php

namespace App\Filament\Admin\Resources\Clients\Tables;

use App\Models\User;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class ClientsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('company_name')->label('Company')->searchable()->sortable()->extraCellAttributes(['class' => 'font-semibold']),
                TextColumn::make('industry')->searchable()->toggleable()->placeholder('—'),
                TextColumn::make('contacts_count')->counts('contacts')->label('Contacts')->badge(),
                TextColumn::make('accountManager.name')->label('Manager')->searchable()->toggleable()->placeholder('—'),
                TextColumn::make('defaultCurrency.code')->label('Currency')->badge()->color('gray'),
                TextColumn::make('status')->badge()->color(fn (string $state): string => match ($state) {
                    'active' => 'success',
                    'inactive' => 'gray',
                    'prospect' => 'warning',
                    default => 'gray',
                }),
                TextColumn::make('phone')->toggleable()->placeholder('—'),
                TextColumn::make('billing_city')->label('City')->toggleable()->placeholder('—'),
                TextColumn::make('created_at')->dateTime('d M Y')->sortable()->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')->options(['active' => 'Active', 'inactive' => 'Inactive', 'prospect' => 'Prospect']),
                SelectFilter::make('account_manager_id')->label('Account Manager')->options(fn () => User::where('is_active', true)->pluck('name', 'id'))->searchable(),
                TrashedFilter::make(),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ])
            ->defaultSort('company_name', 'asc');
    }
}
