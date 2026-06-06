<?php

namespace App\Filament\Admin\Actions;

use App\Models\Lead;
use App\Models\LeadSource;
use App\Models\LeadStatus;
use App\Services\CsvImportService;
use Filament\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Storage;

class ImportLeadsAction extends Action
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
            ->modalDescription('Upload CSV with columns: name, email, phone, company, source, status, assigned_to_email, notes')
            ->form([
                FileUpload::make('csv')
                    ->disk('local')
                    ->directory('imports/leads')
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
                    'name' => ['required', 'string', 'max:255'],
                    'email' => ['required', 'email', 'max:255'],
                ]);

                $defaultStatus = LeadStatus::orderBy('order')->value('id');
                $result = $service->import(
                    modelClass: Lead::class,
                    rows: $validRows,
                    columnMap: [
                        'name' => 'name',
                        'company' => 'company',
                        'email' => 'email',
                        'phone' => 'phone',
                        'notes' => 'description',
                    ],
                    defaults: [
                        'lead_status_id' => $defaultStatus,
                        'last_activity_at' => now(),
                    ],
                    beforeCreate: function (array $data, array $row) {
                        if (! empty($row['source'])) {
                            $data['lead_source_id'] = LeadSource::firstOrCreate(
                                ['name' => trim($row['source'])],
                                ['is_active' => true],
                            )->id;
                        }

                        if (! empty($row['assigned_to_email'])) {
                            $user = \App\Models\User::where('email', trim($row['assigned_to_email']))->first();
                            if ($user) {
                                $data['assigned_to'] = $user->id;
                            }
                        }

                        if (! empty($row['status'])) {
                            $status = LeadStatus::where('name', trim($row['status']))->first();
                            if ($status) {
                                $data['lead_status_id'] = $status->id;
                            }
                        }

                        $existing = Lead::where('email', $data['email'])->exists();
                        if ($existing) {
                            throw new \RuntimeException("Email {$data['email']} already exists");
                        }

                        return $data;
                    },
                );

                $allErrors = array_merge($errors, $result['errors']);
                Storage::disk('local')->delete($data['csv'] ?? '');

                Notification::make()
                    ->title("Imported {$result['count']} leads")
                    ->body($allErrors ? 'Errors: ' . implode('; ', array_slice($allErrors, 0, 5)) : null)
                    ->success()
                    ->send();
            });
    }
}
