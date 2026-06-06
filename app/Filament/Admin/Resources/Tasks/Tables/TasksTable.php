<?php

namespace App\Filament\Admin\Resources\Tasks\Tables;

use App\Models\Project;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class TasksTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')->searchable()->sortable()->extraCellAttributes(['class' => 'font-semibold'])->wrap()->limit(60),
                TextColumn::make('project.name')->label('Project')->searchable()->placeholder('—')->toggleable(),
                TextColumn::make('parent.title')->label('Parent')->placeholder('—')->toggleable(),
                TextColumn::make('subtask_count')->label('Subtasks')->state(fn ($record) => $record->subtask_count)->placeholder('—')->toggleable(),
                TextColumn::make('milestone.name')->label('Milestone')->placeholder('—')->toggleable(),
                TextColumn::make('priority')->badge()->color(fn (string $s) => match ($s) {
                    'urgent' => 'danger', 'high' => 'warning', 'medium' => 'primary', default => 'gray',
                }),
                TextColumn::make('status')->badge()->color(fn (string $s) => match ($s) {
                    'done' => 'success', 'in_progress' => 'warning', 'in_review' => 'info',
                    'cancelled' => 'danger', default => 'gray',
                })->formatStateUsing(fn ($s) => str_replace('_', ' ', ucfirst($s))),
                TextColumn::make('due_date')->date('d M Y')->sortable()->placeholder('—')
                    ->color(fn ($record) => $record->due_date && $record->due_date->isPast() && $record->status !== 'done' ? 'danger' : null),
                TextColumn::make('assignees.name')->label('Assigned')->badge()->color('gray')->listWithLineBreaks()->limitList(2)->toggleable(),
                TextColumn::make('estimate_hours')->label('Est.')->suffix('h')->placeholder('—')->toggleable(),
            ])
            ->filters([
                SelectFilter::make('project_id')->label('Project')->options(fn () => Project::pluck('name', 'id'))->searchable(),
                SelectFilter::make('status')->options([
                    'todo' => 'To Do', 'in_progress' => 'In Progress', 'in_review' => 'In Review',
                    'done' => 'Done', 'cancelled' => 'Cancelled',
                ]),
                SelectFilter::make('priority')->options([
                    'low' => 'Low', 'medium' => 'Medium', 'high' => 'High', 'urgent' => 'Urgent',
                ]),
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
            ->defaultSort('due_date', 'asc');
    }
}
