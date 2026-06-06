<?php

namespace App\Filament\Admin\Pages;

use App\Models\Setting;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

class Settings extends Page implements HasForms
{
    use InteractsWithForms;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCog6Tooth;

    protected static ?string $navigationLabel = 'Settings';

    protected static string|\UnitEnum|null $navigationGroup = 'Sistem';

    protected static ?int $navigationSort = 5;

    protected string $view = 'filament.admin.pages.settings';

    public array $data = [];

    public function mount(): void
    {
        $this->form->fill([
            'company_name' => Setting::get('company_name', config('app.name')),
            'company_email' => Setting::get('company_email'),
            'company_phone' => Setting::get('company_phone'),
            'company_address' => Setting::get('company_address'),
            'default_currency' => Setting::get('default_currency', 'IDR'),
            'default_language' => Setting::get('default_language', 'id'),
            'invoice_number_prefix' => Setting::get('invoice_number_prefix', 'INV-'),
            'estimate_number_prefix' => Setting::get('estimate_number_prefix', 'EST-'),
            'ticket_number_prefix' => Setting::get('ticket_number_prefix', 'TKT-'),
            'dunning_enabled' => (bool) Setting::get('dunning_enabled', true),
            'dunning_cadence_days' => Setting::get('dunning_cadence_days', '7,14,30'),
        ]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Company')
                    ->schema([
                        TextInput::make('company_name')->required(),
                        TextInput::make('company_email')->email(),
                        TextInput::make('company_phone'),
                        Textarea::make('company_address')->rows(2),
                    ])
                    ->columns(2),
                Section::make('Defaults')
                    ->schema([
                        Select::make('default_currency')
                            ->options(\App\Models\Currency::pluck('code', 'code'))
                            ->required(),
                        Select::make('default_language')
                            ->options(['id' => 'Bahasa Indonesia', 'en' => 'English'])
                            ->required(),
                    ])
                    ->columns(2),
                Section::make('Number sequences')
                    ->schema([
                        TextInput::make('invoice_number_prefix'),
                        TextInput::make('estimate_number_prefix'),
                        TextInput::make('ticket_number_prefix'),
                    ])
                    ->columns(3),
                Section::make('Dunning')
                    ->schema([
                        Toggle::make('dunning_enabled'),
                        TextInput::make('dunning_cadence_days')
                            ->helperText('Comma-separated days past due to send reminders, e.g. 7,14,30'),
                    ]),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        $data = $this->form->getState();
        foreach ($data as $key => $value) {
            Setting::set($key, $value);
        }

        Notification::make()
            ->title('Settings saved.')
            ->success()
            ->send();
    }

    protected function getFormActions(): array
    {
        return [
            Action::make('save')->label('Save')->submit('save'),
        ];
    }
}
