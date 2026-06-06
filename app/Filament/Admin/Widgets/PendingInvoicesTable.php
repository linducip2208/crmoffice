<?php

namespace App\Filament\Admin\Widgets;

use App\Models\Invoice;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;

class PendingInvoicesTable extends TableWidget
{
    use DashboardWidgetFilter;

    protected static ?int $sort = 4;
    protected int|string|array $columnSpan = 'full';
    protected static ?string $heading = 'Pending Invoices';

    protected static function isVisibleToRole(array $roles): bool
    {
        return !empty(array_intersect($roles, ['owner', 'admin', 'accountant', 'sales']));
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Invoice::query()
                    ->whereIn('status', ['sent', 'partial', 'overdue'])
                    ->with('client')
                    ->latest('due_date')
            )
            ->columns([
                TextColumn::make('number')->searchable(),
                TextColumn::make('client.company_name')->label('Client'),
                TextColumn::make('total')->money('IDR'),
                TextColumn::make('balance_due')->money('IDR'),
                TextColumn::make('status')->badge(),
                TextColumn::make('due_date')->date('d M Y')->sinceTooltip(),
            ])
            ->paginated(false)
            ->defaultSort('due_date', 'asc');
    }
}
