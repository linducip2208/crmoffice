<?php

namespace App\Filament\Admin\Actions;

use App\Models\Client;
use App\Models\Currency;
use App\Services\CsvImportService;
use Filament\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Storage;

class ImportClientsAction extends Action
{
    public static function getDefaultName(): ?string
    {
        return 'importCsv';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this
            ->label('Import CSV')
            ->icon('heroicon-o-arrow-up-tray')
            ->color('gray')
            ->modalDescription('Upload CSV with columns: company_name, email, phone, address, city, state, zip, country, industry, website')
            ->form([
                FileUpload::make('csv')
                    ->disk('local')
                    ->directory('imports/clients')
                    ->required()
                    ->acceptedFileTypes(['text/csv', 'text/plain', 'application/csv', 'application/vnd.ms-excel']),
            ])
            ->action(function (array $data) {
                $path = storage_path('app/private/' . $data['csv']);
                if (! file_exists($path)) {
                    $path = storage_path('app/' . $data['csv']);
                }

                if (! file_exists($path)) {
                    Notification::make()->title('CSV file not found')->danger()->send();

                    return;
                }

                $service = app(CsvImportService::class);
                $parsed = $service->parse($path);

                if (empty($parsed['header']) || empty($parsed['rows'])) {
                    Notification::make()->title('CSV is empty or invalid')->warning()->send();

                    return;
                }

                [$validRows, $errors] = $service->validateRows($parsed['rows'], [
                    'company_name' => ['required', 'string', 'max:255'],
                    'email' => ['nullable', 'email', 'max:255'],
                ]);

                $defaultCurrency = Currency::where('is_base', true)->value('id');
                $result = $service->import(
                    modelClass: Client::class,
                    rows: $validRows,
                    columnMap: [
                        'company_name' => 'company_name',
                        'email' => function ($row) {
                            return []; // Client email is on contacts, stored as billing_address
                        },
                        'phone' => 'phone',
                        'address' => 'billing_address',
                        'city' => 'billing_city',
                        'state' => 'billing_state',
                        'zip' => 'billing_postal',
                        'country' => 'billing_country',
                        'industry' => 'industry',
                        'website' => 'website',
                    ],
                    defaults: [
                        'default_currency_id' => $defaultCurrency,
                        'default_language' => 'id',
                        'status' => 'active',
                    ],
                );

                $allErrors = array_merge($errors, $result['errors']);
                Storage::disk('local')->delete($data['csv'] ?? '');

                Notification::make()
                    ->title("Imported {$result['count']} clients")
                    ->body($allErrors ? 'Errors: ' . implode('; ', array_slice($allErrors, 0, 5)) : null)
                    ->success()
                    ->send();
            });
    }
}
