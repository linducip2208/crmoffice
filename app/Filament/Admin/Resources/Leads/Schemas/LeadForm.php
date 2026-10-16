<?php

namespace App\Filament\Admin\Resources\Leads\Schemas;

use App\Filament\Support\CustomFieldsRenderer;
use App\Models\Currency;
use App\Models\LeadSource;
use App\Models\LeadStatus;
use App\Models\User;
use Filament\Forms\Components\DatePicker;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class LeadForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Contact')->schema([
                Grid::make(2)->schema([
                    TextInput::make('name')->label(__('crm.field.name'))->required()->maxLength(180),
                    TextInput::make('company')->label(__('crm.field.company_name'))->maxLength(255),
                    TextInput::make('email')->label(__('crm.field.email'))->email()->maxLength(255),
                    TextInput::make('phone')->label(__('crm.field.phone'))->tel()->maxLength(40),
                    TextInput::make('website')->label(__('crm.field.website'))->url()->maxLength(255)->prefix('https://'),
                ]),
            ]),

            Section::make('Pipeline')->schema([
                Grid::make(3)->schema([
                    Select::make('lead_status_id')->label(__('crm.field.status_id'))
                        ->options(fn () => LeadStatus::orderBy('order')->pluck('name', 'id'))
                        ->required()
                        ->default(fn () => LeadStatus::orderBy('order')->value('id')),
                    Select::make('lead_source_id')->label(__('crm.field.source'))
                        ->options(fn () => LeadSource::where('is_active', true)->pluck('name', 'id')),
                    Select::make('assigned_to')->label(__('crm.field.assigned_to'))
                        ->options(fn () => User::where('is_active', true)->pluck('name', 'id'))
                        ->searchable(),
                    TextInput::make('estimated_value')->label(__('crm.field.value'))->numeric()->prefix('Rp')->minValue(0),
                    Select::make('currency_id')->label(__('crm.field.currency'))
                        ->options(fn () => Currency::pluck('code', 'id'))
                        ->default(fn () => Currency::where('is_base', true)->value('id')),
                    DatePicker::make('expected_close')->label(__('crm.field.expected_close'))->displayFormat('d M Y'),
                ]),
            ]),

            Section::make('Address')->schema([
                Grid::make(3)->schema([
                    Textarea::make('address')->label(__('crm.field.address'))->rows(2)->columnSpanFull(),
                    TextInput::make('city')->label(__('crm.field.city'))->maxLength(120),
                    TextInput::make('country')->label(__('crm.field.country'))->maxLength(2),
                ]),
            ])->collapsed(),

            Section::make('Description')->schema([
                Textarea::make('description')->label(__('crm.field.description'))->rows(4)->columnSpanFull(),
            ])->collapsed(),

            ...CustomFieldsRenderer::section('leads'),
        ]);
    }
}
