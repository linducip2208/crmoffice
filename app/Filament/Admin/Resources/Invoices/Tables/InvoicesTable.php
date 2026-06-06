<?php

namespace App\Filament\Admin\Resources\Invoices\Tables;

use App\Filament\Admin\Actions\BulkSendInvoiceAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class InvoicesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('number')->searchable()->sortable()->extraCellAttributes(['class' => 'font-semibold']),
                TextColumn::make('client.company_name')->label('Client')->searchable()->sortable(),
                TextColumn::make('invoice_date')->date('d M Y')->sortable(),
                TextColumn::make('due_date')->date('d M Y')->sortable()
                    ->color(fn ($record) => $record->due_date && $record->due_date->isPast() && $record->status !== 'paid' ? 'danger' : null),
                TextColumn::make('currency.code')->label('Cur')->badge()->color('gray'),
                TextColumn::make('total')->money('IDR')->sortable(),
                TextColumn::make('paid_total')->money('IDR')->toggleable(),
                TextColumn::make('balance_due')->money('IDR')->toggleable(),
                TextColumn::make('status')->badge()->color(fn (string $state): string => match ($state) {
                    'paid' => 'success',
                    'sent', 'partial' => 'warning',
                    'overdue' => 'danger',
                    'void' => 'gray',
                    default => 'primary',
                }),
                TextColumn::make('is_recurring')->label('Recurring')->badge()->color('info')->formatStateUsing(fn ($state) => $state ? 'Yes' : '')->toggleable(),
            ])
            ->filters([
                SelectFilter::make('status')->options([
                    'draft' => 'Draft', 'sent' => 'Sent', 'partial' => 'Partial',
                    'paid' => 'Paid', 'overdue' => 'Overdue', 'void' => 'Void',
                ]),
                TrashedFilter::make(),
            ])
            ->recordActions([EditAction::make()])
            ->toolbarActions([
                BulkActionGroup::make([
                    BulkSendInvoiceAction::make(),
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ])
            ->defaultSort('invoice_date', 'desc');
    }
}
