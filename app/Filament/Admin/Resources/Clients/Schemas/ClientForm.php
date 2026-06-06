<?php

namespace App\Filament\Admin\Resources\Clients\Schemas;

use App\Filament\Support\CustomFieldsRenderer;
use App\Models\Currency;
use App\Models\User;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Tabs;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class ClientForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Tabs::make()->tabs([
                Tabs\Tab::make('Overview')->schema([
                    Section::make('Company')->schema([
                        Grid::make(2)->schema([
                            TextInput::make('company_name')->label(__('crm.fields.company_name'))->required()->maxLength(255)->columnSpan(2),
                            TextInput::make('industry')->label(__('crm.fields.industry'))->maxLength(120),
                            TextInput::make('website')->label(__('crm.fields.website'))->url()->maxLength(255)->prefix('https://'),
                            TextInput::make('phone')->label(__('crm.fields.phone'))->tel()->maxLength(40),
                            TextInput::make('tax_id')->label(__('crm.fields.vat_number'))->maxLength(60),
                        ]),
                    ]),
                    Section::make('Account Settings')->schema([
                        Grid::make(3)->schema([
                            Select::make('account_manager_id')->label(__('crm.fields.assigned_to'))->options(fn () => User::where('is_active', true)->pluck('name', 'id'))->searchable(),
                            Select::make('default_currency_id')->label(__('crm.fields.currency'))->options(fn () => Currency::pluck('code', 'id'))->required()->default(fn () => Currency::where('is_base', true)->value('id')),
                            Select::make('default_language')->label(__('crm.fields.locale'))->options(['id' => 'Indonesia', 'en' => 'English'])->default('id')->required(),
                            Select::make('status')->label(__('crm.fields.status'))->options(['active' => __('crm.status.active'), 'inactive' => __('crm.status.inactive'), 'prospect' => 'Prospect'])->default('active')->required(),
                        ]),
                    ]),
                    Section::make('Internal Notes')->schema([
                        Textarea::make('notes')->label(__('crm.fields.notes'))->rows(4)->columnSpanFull(),
                    ])->collapsed(),
                ]),

                Tabs\Tab::make(__('crm.fields.billing_address'))->schema([
                    Textarea::make('billing_address')->label(__('crm.fields.address'))->rows(2)->columnSpanFull(),
                    Grid::make(2)->schema([
                        TextInput::make('billing_city')->label(__('crm.fields.city')),
                        TextInput::make('billing_state')->label(__('crm.fields.state')),
                        TextInput::make('billing_country')->label(__('crm.fields.country'))->maxLength(2)->placeholder('ID'),
                        TextInput::make('billing_postal')->label(__('crm.fields.postal_code')),
                    ]),
                ]),

                Tabs\Tab::make(__('crm.fields.shipping_address'))->schema([
                    Textarea::make('shipping_address')->label(__('crm.fields.address'))->rows(2)->columnSpanFull(),
                    Grid::make(2)->schema([
                        TextInput::make('shipping_city')->label(__('crm.fields.city')),
                        TextInput::make('shipping_state')->label(__('crm.fields.state')),
                        TextInput::make('shipping_country')->label(__('crm.fields.country'))->maxLength(2)->placeholder('ID'),
                        TextInput::make('shipping_postal')->label(__('crm.fields.postal_code')),
                    ]),
                ]),
            ])->columnSpanFull(),
            ...CustomFieldsRenderer::section('clients'),
        ]);
    }
}
