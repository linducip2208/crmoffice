<?php

namespace App\Filament\Admin\Resources\Leads\Tables;

use App\Filament\Admin\Actions\BulkAssignAction;
use App\Filament\Admin\Actions\BulkScoreLeadsAction;
use App\Models\LeadSource;
use App\Models\LeadStatus;
use App\Models\User;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class LeadsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->searchable()->sortable()->extraCellAttributes(['class' => 'font-semibold']),
                TextColumn::make('company')->searchable()->placeholder('—'),
                TextColumn::make('email')->searchable()->placeholder('—')->copyable(),
                TextColumn::make('phone')->placeholder('—')->toggleable(),
                TextColumn::make('status.name')
                    ->label('Status')
                    ->badge()
                    ->color(fn ($record) => match (true) {
                        $record->status?->is_won => 'success',
                        $record->status?->is_lost => 'danger',
                        default => 'primary',
                    }),
                TextColumn::make('lead_score')
                    ->label('AI Score')
                    ->badge()
                    ->color(function ($record) {
                        $level = $record->lead_score_level;
                        return match ($level) {
                            'hot' => 'danger',
                            'warm' => 'warning',
                            default => 'gray',
                        };
                    })
                    ->tooltip(function ($record) {
                        $factors = $record->lead_score_factors;
                        if (! $factors) {
                            return null;
                        }
                        $recommendation = $factors['recommendation'] ?? '';
                        $aiReasoning = $factors['ai_reasoning'] ?? '';
                        $parts = [];
                        if ($recommendation) {
                            $parts[] = "Rekomendasi: {$recommendation}";
                        }
                        if ($aiReasoning) {
                            $parts[] = "AI: {$aiReasoning}";
                        }
                        return implode("\n", $parts);
                    })
                    ->toggleable()
                    ->placeholder('—'),
                TextColumn::make('source.name')->label('Source')->badge()->color('gray')->placeholder('—')->toggleable(),
                TextColumn::make('assignedTo.name')->label('Assigned')->placeholder('—')->toggleable(),
                TextColumn::make('estimated_value')->label('Value')->money('IDR')->sortable()->placeholder('—'),
                TextColumn::make('expected_close')->date('d M Y')->sortable()->placeholder('—')->toggleable(),
                TextColumn::make('created_at')->dateTime('d M Y')->sortable()->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('lead_status_id')->label('Status')->options(fn () => LeadStatus::orderBy('order')->pluck('name', 'id')),
                SelectFilter::make('lead_source_id')->label('Source')->options(fn () => LeadSource::pluck('name', 'id')),
                SelectFilter::make('assigned_to')->label('Assigned')->options(fn () => User::pluck('name', 'id')),
                TrashedFilter::make(),
            ])
            ->recordActions([EditAction::make()])
            ->toolbarActions([
                BulkActionGroup::make([
                    BulkAssignAction::make(),
                    BulkScoreLeadsAction::make(),
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
