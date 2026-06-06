<?php

namespace App\Filament\Admin\Widgets;

use App\Models\Task;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;

class MyTasksTable extends TableWidget
{
    use DashboardWidgetFilter;

    protected static ?int $sort = 5;
    protected int|string|array $columnSpan = 'full';
    protected static ?string $heading = 'My Tasks';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Task::query()
                    ->whereHas('assignees', fn ($q) => $q->where('user_id', auth()->id()))
                    ->whereNotIn('status', ['done', 'cancelled'])
                    ->with('project')
                    ->latest('due_date')
            )
            ->columns([
                TextColumn::make('title')->searchable()->limit(40),
                TextColumn::make('project.name')->label('Project'),
                TextColumn::make('priority')->badge(),
                TextColumn::make('status')->badge(),
                TextColumn::make('due_date')->date('d M Y')->sinceTooltip(),
            ])
            ->paginated(false)
            ->defaultSort('priority_order', 'asc');
    }
}
