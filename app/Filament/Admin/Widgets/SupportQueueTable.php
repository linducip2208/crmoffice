<?php

namespace App\Filament\Admin\Widgets;

use App\Models\Ticket;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;

class SupportQueueTable extends TableWidget
{
    use DashboardWidgetFilter;

    protected static ?int $sort = 6;
    protected int|string|array $columnSpan = 'full';
    protected static ?string $heading = 'Open Tickets';

    protected static function isVisibleToRole(array $roles): bool
    {
        return !empty(array_intersect($roles, ['owner', 'admin', 'support']));
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Ticket::query()
                    ->whereNull('resolved_at')
                    ->whereNull('closed_at')
                    ->with(['client', 'priority', 'assignedTo'])
                    ->latest()
            )
            ->columns([
                TextColumn::make('number')->searchable(),
                TextColumn::make('subject')->searchable()->limit(40),
                TextColumn::make('client.company_name')->label('Client'),
                TextColumn::make('priority.name')->badge(),
                TextColumn::make('assignedTo.name')->label('Agent'),
                TextColumn::make('created_at')->date('d M Y')->sinceTooltip(),
            ])
            ->paginated(false)
            ->defaultSort('created_at', 'asc');
    }
}
