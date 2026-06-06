<?php

namespace App\Filament\Admin\Resources\Tickets\Tables;

use App\Models\Department;
use App\Models\TicketPriority;
use App\Models\TicketStatus;
use App\Models\User;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class TicketsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('number')->searchable()->sortable()->extraCellAttributes(['class' => 'font-semibold']),
                TextColumn::make('subject')->searchable()->wrap()->limit(60),
                TextColumn::make('client.company_name')->label('Client')->placeholder('—')->toggleable(),
                TextColumn::make('department.name')->label('Dept')->badge()->color('gray')->toggleable(),
                TextColumn::make('priority.name')->label('Priority')->badge()
                    ->color(fn ($record) => match ($record->priority?->name) {
                        'Urgent' => 'danger',
                        'High' => 'warning',
                        'Medium' => 'primary',
                        default => 'gray',
                    }),
                TextColumn::make('status.name')->label('Status')->badge()
                    ->color(fn ($record) => match (true) {
                        $record->status?->is_resolved => 'success',
                        $record->status?->is_open => 'primary',
                        default => 'gray',
                    }),
                TextColumn::make('assignee.name')->label('Assigned')->placeholder('—')->toggleable(),
                TextColumn::make('first_response_due_at')->label('SLA Response')->dateTime('d M Y H:i')->placeholder('—')
                    ->color(fn ($record) => $record->first_response_due_at && $record->first_response_due_at->isPast() && ! $record->first_response_at ? 'danger' : null)
                    ->toggleable(),
                TextColumn::make('created_at')->dateTime('d M Y H:i')->sortable()->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status_id')->label('Status')->options(fn () => TicketStatus::pluck('name', 'id')),
                SelectFilter::make('priority_id')->label('Priority')->options(fn () => TicketPriority::pluck('name', 'id')),
                SelectFilter::make('department_id')->label('Department')->options(fn () => Department::pluck('name', 'id')),
                SelectFilter::make('assigned_to')->label('Assigned')->options(fn () => User::pluck('name', 'id')),
                TrashedFilter::make(),
            ])
            ->recordActions([EditAction::make()])
            ->toolbarActions([
                BulkActionGroup::make([
                    BulkAction::make('changeStatus')
                        ->label('Change Status')
                        ->icon('heroicon-o-arrows-right-left')
                        ->color('warning')
                        ->form([
                            Select::make('status_id')
                                ->label('New Status')
                                ->options(fn () => TicketStatus::pluck('name', 'id'))
                                ->required(),
                        ])
                        ->action(function ($records, array $data) {
                            $count = 0;
                            $statusName = TicketStatus::find($data['status_id'])?->name ?? 'Unknown';
                            foreach ($records as $ticket) {
                                $ticket->update(['status_id' => $data['status_id']]);
                                $count++;
                            }
                            Notification::make()->title("Changed $count tickets to \"$statusName\"")->success()->send();
                        }),
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
