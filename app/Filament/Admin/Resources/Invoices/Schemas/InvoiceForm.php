<?php

namespace App\Filament\Admin\Resources\Invoices\Schemas;

use App\Models\Client;
use App\Models\Currency;
use App\Models\Item;
use App\Models\Project;
use App\Models\TaxRate;
use App\Services\NumberSequence;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class InvoiceForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Invoice Details')->schema([
                Grid::make(3)->schema([
                    TextInput::make('number')
                        ->label(__('crm.fields.number'))
                        ->required()->maxLength(40)->unique(ignoreRecord: true)
                        ->default(fn () => NumberSequence::peek('invoice'))
                        ->helperText('Auto-generated; editable before save.'),
                    Select::make('client_id')->label(__('crm.fields.client_id'))
                        ->options(fn () => Client::orderBy('company_name')->pluck('company_name', 'id'))
                        ->searchable()->required(),
                    Select::make('project_id')->label(__('crm.fields.project_id'))
                        ->options(fn () => Project::orderBy('name')->pluck('name', 'id'))->searchable(),
                    DatePicker::make('invoice_date')->label(__('crm.fields.invoice_date'))->required()->default(now())->displayFormat('d M Y'),
                    DatePicker::make('due_date')->label(__('crm.fields.due_date'))->required()->default(now()->addDays(14))->displayFormat('d M Y'),
                    Select::make('currency_id')->label(__('crm.fields.currency'))
                        ->options(fn () => Currency::pluck('code', 'id'))
                        ->required()->default(fn () => Currency::where('is_base', true)->value('id')),
                    Select::make('status')
                        ->label(__('crm.fields.invoice_status'))
                        ->options([
                            'draft' => __('crm.status.draft'), 'sent' => __('crm.status.sent'), 'partial' => __('crm.status.partial'),
                            'paid' => __('crm.status.paid'), 'overdue' => __('crm.status.overdue'), 'void' => 'Void',
                        ])->default('draft')->required(),
                ]),
            ]),

            Section::make('Line Items')->schema([
                Repeater::make('items')->relationship()->schema([
                    Grid::make(12)->schema([
                        Select::make('item_id')->label(__('crm.fields.item_id'))
                            ->options(fn () => Item::where('is_active', true)->pluck('name', 'id'))
                            ->searchable()->columnSpan(3)
                            ->afterStateUpdated(function ($state, $set) {
                                if (! $state) {
                                    return;
                                }
                                $item = Item::find($state);
                                if ($item) {
                                    $set('description', $item->name);
                                    $set('unit_price', $item->default_price);
                                    $set('tax_rate_id', $item->default_tax_rate_id);
                                }
                            })->live(),
                        TextInput::make('description')->label(__('crm.fields.description'))->required()->columnSpan(4),
                        TextInput::make('quantity')->label(__('crm.fields.quantity'))->numeric()->default(1)->required()->columnSpan(1)->minValue(0),
                        TextInput::make('unit_price')->label(__('crm.fields.unit_price'))->numeric()->prefix('Rp')->required()->columnSpan(2)->minValue(0),
                        Select::make('tax_rate_id')->label(__('crm.fields.tax'))
                            ->options(fn () => TaxRate::where('is_active', true)->pluck('name', 'id'))
                            ->columnSpan(2),
                    ]),
                ])->columnSpanFull()->defaultItems(1)->collapsible()->cloneable()->reorderable()->addActionLabel('Add Line Item'),
            ]),

            Section::make('Totals')->schema([
                Grid::make(4)->schema([
                    TextInput::make('subtotal')->label(__('crm.fields.subtotal'))->numeric()->prefix('Rp')->default(0)->readOnly(),
                    TextInput::make('discount_total')->label(__('crm.fields.discount_amount'))->numeric()->prefix('Rp')->default(0),
                    TextInput::make('tax_total')->label(__('crm.fields.tax_amount'))->numeric()->prefix('Rp')->default(0)->readOnly(),
                    TextInput::make('total')->label(__('crm.fields.total'))->numeric()->prefix('Rp')->default(0)->readOnly()->extraInputAttributes(['style' => 'font-weight:700']),
                ]),
            ])->collapsed(),

            Section::make('Recurring')->schema([
                Grid::make(3)->schema([
                    Toggle::make('is_recurring')->label(__('crm.fields.is_recurring')),
                    Select::make('recurring_period')->label(__('crm.fields.recurring_period'))->options([
                        'daily' => 'Daily', 'weekly' => 'Weekly', 'monthly' => 'Monthly',
                        'quarterly' => 'Quarterly', 'yearly' => 'Yearly',
                    ])->visible(fn ($get) => $get('is_recurring')),
                    DatePicker::make('next_recurring_date')->label(__('crm.fields.next_recurring_date'))->visible(fn ($get) => $get('is_recurring')),
                ]),
            ])->collapsed(),

            Section::make('Notes & Terms')->schema([
                Textarea::make('notes')->label(__('crm.fields.notes'))->rows(3)->columnSpanFull(),
                Textarea::make('terms')->label(__('crm.fields.terms'))->rows(3)->columnSpanFull(),
            ])->collapsed(),
        ]);
    }
}
