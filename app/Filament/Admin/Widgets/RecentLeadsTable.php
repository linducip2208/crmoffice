<?php

namespace App\Filament\Admin\Widgets;

use App\Models\Lead;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class RecentLeadsTable extends BaseWidget
{
    use DashboardWidgetFilter;

    protected static ?int $sort = 3;

    protected int|string|array $columnSpan = 'full';

    protected static ?string $heading = 'Recent leads';

    public function table(Table $table): Table
    {
        return $table
            ->query(Lead::query()->latest()->limit(8))
            ->columns([
                TextColumn::make('name')->searchable(),
                TextColumn::make('company'),
                TextColumn::make('status.name')->badge(),
                TextColumn::make('estimated_value')->numeric()->sortable(),
                TextColumn::make('created_at')->since(),
            ])
            ->paginated(false);
    }
}
