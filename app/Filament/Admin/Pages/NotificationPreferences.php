<?php

namespace App\Filament\Admin\Pages;

use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

class NotificationPreferences extends Page implements HasForms
{
    use InteractsWithForms;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBellAlert;

    protected static ?string $navigationLabel = 'Notifikasi';

    protected static string|\UnitEnum|null $navigationGroup = 'Sistem';

    protected static ?int $navigationSort = 6;

    protected string $view = 'filament.admin.pages.notification-preferences';

    public array $data = [];

    public function mount(): void
    {
        $prefs = auth()->user()->notification_preferences ?? [];

        $this->form->fill([
            'task_assigned_database' => $prefs['task.assigned']['database'] ?? true,
            'task_assigned_mail' => $prefs['task.assigned']['mail'] ?? true,
            'ticket_assigned_database' => $prefs['ticket.assigned']['database'] ?? true,
            'ticket_assigned_mail' => $prefs['ticket.assigned']['mail'] ?? true,
            'invoice_paid_database' => $prefs['invoice.paid']['database'] ?? true,
            'invoice_paid_mail' => $prefs['invoice.paid']['mail'] ?? true,
            'invoice_overdue_database' => $prefs['invoice.overdue']['database'] ?? true,
            'invoice_overdue_mail' => $prefs['invoice.overdue']['mail'] ?? true,
            'contract_expiring_database' => $prefs['contract.expiring']['database'] ?? true,
            'contract_expiring_mail' => $prefs['contract.expiring']['mail'] ?? true,
            'sla_breached_database' => $prefs['sla.breached']['database'] ?? true,
            'sla_breached_mail' => $prefs['sla.breached']['mail'] ?? true,
            'lead_created_database' => $prefs['lead.created']['database'] ?? true,
            'lead_created_mail' => $prefs['lead.created']['mail'] ?? true,
        ]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Task')
                    ->description('Notifikasi terkait tugas dan pekerjaan')
                    ->schema([
                        Toggle::make('task_assigned_database')->label('Database')->inline(false),
                        Toggle::make('task_assigned_mail')->label('Email')->inline(false),
                    ])
                    ->columns(2),
                Section::make('Ticket')
                    ->description('Notifikasi terkait tiket support')
                    ->schema([
                        Toggle::make('ticket_assigned_database')->label('Database')->inline(false),
                        Toggle::make('ticket_assigned_mail')->label('Email')->inline(false),
                    ])
                    ->columns(2),
                Section::make('Invoice')
                    ->description('Notifikasi terkait invoice dan pembayaran')
                    ->schema([
                        Toggle::make('invoice_paid_database')->label('Database — Invoice Dibayar')->inline(false),
                        Toggle::make('invoice_paid_mail')->label('Email — Invoice Dibayar')->inline(false),
                        Toggle::make('invoice_overdue_database')->label('Database — Invoice Jatuh Tempo')->inline(false),
                        Toggle::make('invoice_overdue_mail')->label('Email — Invoice Jatuh Tempo')->inline(false),
                    ])
                    ->columns(2),
                Section::make('Kontrak')
                    ->description('Notifikasi terkait kontrak yang akan berakhir')
                    ->schema([
                        Toggle::make('contract_expiring_database')->label('Database')->inline(false),
                        Toggle::make('contract_expiring_mail')->label('Email')->inline(false),
                    ])
                    ->columns(2),
                Section::make('SLA')
                    ->description('Notifikasi terkait pelanggaran SLA')
                    ->schema([
                        Toggle::make('sla_breached_database')->label('Database')->inline(false),
                        Toggle::make('sla_breached_mail')->label('Email')->inline(false),
                    ])
                    ->columns(2),
                Section::make('Lead')
                    ->description('Notifikasi terkait lead baru')
                    ->schema([
                        Toggle::make('lead_created_database')->label('Database')->inline(false),
                        Toggle::make('lead_created_mail')->label('Email')->inline(false),
                    ])
                    ->columns(2),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        $data = $this->form->getState();

        $prefs = [
            'task.assigned' => [
                'database' => (bool) ($data['task_assigned_database'] ?? true),
                'mail' => (bool) ($data['task_assigned_mail'] ?? true),
            ],
            'ticket.assigned' => [
                'database' => (bool) ($data['ticket_assigned_database'] ?? true),
                'mail' => (bool) ($data['ticket_assigned_mail'] ?? true),
            ],
            'invoice.paid' => [
                'database' => (bool) ($data['invoice_paid_database'] ?? true),
                'mail' => (bool) ($data['invoice_paid_mail'] ?? true),
            ],
            'invoice.overdue' => [
                'database' => (bool) ($data['invoice_overdue_database'] ?? true),
                'mail' => (bool) ($data['invoice_overdue_mail'] ?? true),
            ],
            'contract.expiring' => [
                'database' => (bool) ($data['contract_expiring_database'] ?? true),
                'mail' => (bool) ($data['contract_expiring_mail'] ?? true),
            ],
            'sla.breached' => [
                'database' => (bool) ($data['sla_breached_database'] ?? true),
                'mail' => (bool) ($data['sla_breached_mail'] ?? true),
            ],
            'lead.created' => [
                'database' => (bool) ($data['lead_created_database'] ?? true),
                'mail' => (bool) ($data['lead_created_mail'] ?? true),
            ],
        ];

        auth()->user()->update(['notification_preferences' => $prefs]);

        Notification::make()
            ->title('Preferensi notifikasi disimpan.')
            ->success()
            ->send();
    }

    protected function getFormActions(): array
    {
        return [
            Action::make('save')->label('Simpan')->submit('save'),
        ];
    }
}
