<?php

namespace App\Filament\Admin\Resources\Projects\Tables;

use App\Models\User;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class ProjectsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->searchable()->sortable()->extraCellAttributes(['class' => 'font-semibold']),
                TextColumn::make('client.company_name')->label('Client')->searchable()->sortable(),
                TextColumn::make('projectManager.name')->label('PM')->placeholder('—')->toggleable(),
                TextColumn::make('status')->badge()->color(fn (string $state) => match ($state) {
                    'completed' => 'success',
                    'in_progress' => 'primary',
                    'on_hold' => 'warning',
                    'cancelled' => 'danger',
                    default => 'gray',
                })->formatStateUsing(fn ($state) => str_replace('_', ' ', ucfirst($state))),
                TextColumn::make('progress_pct')->label('Progress')->suffix('%')->sortable(),
                TextColumn::make('billing_method')->badge()->color('info')->toggleable(),
                TextColumn::make('start_date')->date('d M Y')->toggleable()->placeholder('—'),
                TextColumn::make('deadline')->date('d M Y')->sortable()->placeholder('—')
                    ->color(fn ($record) => $record->deadline && $record->deadline->isPast() && $record->status !== 'completed' ? 'danger' : null),
                TextColumn::make('tasks_count')->counts('tasks')->label('Tasks')->badge()->toggleable(),
            ])
            ->filters([
                SelectFilter::make('status')->options([
                    'not_started' => 'Not Started', 'in_progress' => 'In Progress',
                    'on_hold' => 'On Hold', 'completed' => 'Completed', 'cancelled' => 'Cancelled',
                ]),
                SelectFilter::make('project_manager_id')->label('PM')->options(fn () => User::pluck('name', 'id')),
                TrashedFilter::make(),
            ])
            ->recordActions([EditAction::make()])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ])
            ->defaultSort('deadline', 'asc');
    }
}
