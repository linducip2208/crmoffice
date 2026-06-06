<?php

namespace App\Filament\Admin\Resources\Projects\Schemas;

use App\Models\Client;
use App\Models\Currency;
use App\Models\User;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class ProjectForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Project')->schema([
                Grid::make(2)->schema([
                    TextInput::make('name')->label(__('crm.fields.name'))->required()->maxLength(255)->columnSpan(2),
                    Select::make('client_id')->label(__('crm.fields.client_id'))
                        ->options(fn () => Client::orderBy('company_name')->pluck('company_name', 'id'))
                        ->searchable()->required(),
                    Select::make('project_manager_id')->label(__('crm.fields.project_manager_id'))
                        ->options(fn () => User::where('is_active', true)->pluck('name', 'id'))
                        ->searchable(),
                    Select::make('status')->label(__('crm.fields.project_status'))->options([
                        'not_started' => 'Not Started',
                        'in_progress' => 'In Progress',
                        'on_hold' => 'On Hold',
                        'completed' => 'Completed',
                        'cancelled' => 'Cancelled',
                    ])->default('not_started')->required(),
                    TextInput::make('progress_pct')->label(__('crm.fields.progress_percent'))->numeric()->minValue(0)->maxValue(100)->default(0)->suffix('%'),
                    Textarea::make('description')->label(__('crm.fields.description'))->rows(3)->columnSpanFull(),
                ]),
            ]),

            Section::make('Schedule & Budget')->schema([
                Grid::make(3)->schema([
                    DatePicker::make('start_date')->label(__('crm.fields.start_date'))->displayFormat('d M Y'),
                    DatePicker::make('deadline')->label(__('crm.fields.due_date'))->displayFormat('d M Y'),
                    TextInput::make('estimate_hours')->label(__('crm.fields.estimated_hours'))->numeric()->minValue(0)->suffix('h'),
                    Select::make('billing_method')->label(__('crm.fields.billing_method'))->options([
                        'fixed' => 'Fixed Price',
                        'hourly' => 'Hourly Rate',
                        'milestone' => 'Per Milestone',
                        'non_billable' => 'Non-Billable',
                    ])->default('fixed')->required()->live(),
                    TextInput::make('fixed_price')->label(__('crm.fields.fixed_price'))->numeric()->prefix('Rp')->visible(fn ($get) => $get('billing_method') === 'fixed'),
                    TextInput::make('hourly_rate')->label(__('crm.fields.hourly_rate'))->numeric()->prefix('Rp')->suffix('/h')->visible(fn ($get) => $get('billing_method') === 'hourly'),
                    Select::make('currency_id')->label(__('crm.fields.currency'))
                        ->options(fn () => Currency::pluck('code', 'id'))->required()
                        ->default(fn () => Currency::where('is_base', true)->value('id')),
                ]),
            ]),

            Section::make('Visibility')->schema([
                Toggle::make('is_visible_to_customer')->label('Visible to customer portal')->default(true),
            ])->collapsed(),
        ]);
    }
}
