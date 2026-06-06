<?php

namespace App\Filament\Admin\Resources\Invoices\Pages;

use App\Actions\Sales\ApplyPaymentToInvoice;
use App\Filament\Admin\Resources\Invoices\InvoiceResource;
use App\Services\AiReminderMessageService;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditInvoice extends EditRecord
{
    protected static string $resource = InvoiceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('generateReminder')
                ->label('Generate Reminder Message')
                ->icon('heroicon-o-chat-bubble-bottom-center-text')
                ->color('warning')
                ->visible(function () {
                    $record = $this->record;

                    return in_array($record->status, ['sent', 'partial', 'overdue'])
                        && $record->balance_due > 0
                        && $record->due_date
                        && $record->due_date->isPast();
                })
                ->form([
                    Select::make('tone')
                        ->label('Tone')
                        ->options([
                            'friendly' => 'Friendly — Pengingat pertama',
                            'firm' => 'Firm — Pengingat kedua/ketiga',
                            'urgent' => 'Urgent — Pemberitahuan final',
                        ])
                        ->default(function () {
                            $daysOverdue = (int) $this->record->due_date->diffInDays(now());
                            return match (true) {
                                $daysOverdue >= 30 => 'urgent',
                                $daysOverdue >= 8 => 'firm',
                                default => 'friendly',
                            };
                        })
                        ->required(),
                ])
                ->action(function (array $data) {
                    $service = app(AiReminderMessageService::class);
                    $message = $service->generateReminder($this->record, $data['tone']);

                    Notification::make()
                        ->title("Reminder Message — {$this->record->number}")
                        ->body($message)
                        ->success()
                        ->persistent()
                        ->send();
                }),

            Action::make('recordPayment')
                ->label('Record Payment')
                ->icon('heroicon-o-banknotes')
                ->color('success')
                ->visible(fn () => ! in_array($this->record->status, ['paid', 'void']))
                ->form([
                    TextInput::make('amount')
                        ->numeric()
                        ->prefix('Rp')
                        ->required()
                        ->default(fn () => $this->record->balance_due),
                    Select::make('method')
                        ->options([
                            'cash' => 'Cash',
                            'bank_transfer' => 'Bank Transfer',
                            'check' => 'Check',
                            'card' => 'Card',
                            'qris' => 'QRIS',
                            'gateway' => 'Online Gateway',
                            'manual' => 'Manual / Other',
                        ])
                        ->default('bank_transfer')
                        ->required(),
                    DateTimePicker::make('paid_at')
                        ->default(now())
                        ->required(),
                    TextInput::make('transaction_id')->label('Reference / Transaction ID'),
                    Textarea::make('note')->rows(2),
                ])
                ->action(function (array $data) {
                    app(ApplyPaymentToInvoice::class)->handle($this->record, $data);

                    Notification::make()
                        ->title('Payment recorded')
                        ->body("Rp " . number_format((float) $data['amount'], 0, ',', '.') . " applied.")
                        ->success()
                        ->send();

                    return redirect(request()->header('Referer'));
                }),

            Action::make('markPaid')
                ->label('Mark as Paid (full)')
                ->icon('heroicon-o-check-circle')
                ->color('success')
                ->visible(fn () => $this->record->balance_due > 0 && ! in_array($this->record->status, ['paid', 'void']))
                ->requiresConfirmation()
                ->action(function () {
                    app(ApplyPaymentToInvoice::class)->handle($this->record, [
                        'amount' => $this->record->balance_due,
                        'method' => 'manual',
                        'note' => 'Marked as paid (full) by ' . auth()->user()?->name,
                    ]);
                    Notification::make()->title('Invoice marked paid')->success()->send();

                    return redirect(request()->header('Referer'));
                }),

            Action::make('void')
                ->label('Void')
                ->icon('heroicon-o-x-circle')
                ->color('danger')
                ->visible(fn () => $this->record->status !== 'void')
                ->requiresConfirmation()
                ->action(function () {
                    $this->record->update(['status' => 'void']);
                    Notification::make()->title('Invoice voided')->warning()->send();

                    return redirect(request()->header('Referer'));
                }),

            Action::make('downloadPdf')
                ->label('Download PDF')
                ->icon('heroicon-o-document-arrow-down')
                ->color('info')
                ->url(fn () => '/admin/invoices/' . $this->record->id . '/pdf', shouldOpenInNewTab: true),

            Action::make('viewPdf')
                ->label('Preview PDF')
                ->icon('heroicon-o-eye')
                ->color('gray')
                ->url(fn () => '/admin/invoices/' . $this->record->id . '/pdf/preview', shouldOpenInNewTab: true),

            Action::make('publicLink')
                ->label('Copy Public Link')
                ->icon('heroicon-o-link')
                ->color('gray')
                ->url(fn () => url("/public/invoices/{$this->record->public_token}"), shouldOpenInNewTab: true),

            DeleteAction::make(),
            ForceDeleteAction::make(),
            RestoreAction::make(),
        ];
    }
}
