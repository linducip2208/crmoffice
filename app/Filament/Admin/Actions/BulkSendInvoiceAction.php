<?php

namespace App\Filament\Admin\Actions;

use App\Jobs\SendInvoiceBulkEmail;
use Filament\Actions\BulkAction;
use Filament\Notifications\Notification;

class BulkSendInvoiceAction extends BulkAction
{
    public static function getDefaultName(): ?string
    {
        return 'bulkSend';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this
            ->label('Send via Email')
            ->icon('heroicon-o-paper-airplane')
            ->color('success')
            ->requiresConfirmation()
            ->modalHeading('Send selected invoices')
            ->modalDescription('Each invoice will be queued for email delivery.')
            ->action(function ($records) {
                $count = 0;
                foreach ($records as $invoice) {
                    if (in_array($invoice->status, ['draft', 'sent', 'partial', 'overdue'])) {
                        SendInvoiceBulkEmail::dispatch($invoice);
                        $count++;
                    }
                }
                Notification::make()
                    ->title("Queued $count invoices for email delivery")
                    ->success()
                    ->send();
            });
    }
}
