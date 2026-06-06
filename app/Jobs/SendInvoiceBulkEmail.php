<?php

namespace App\Jobs;

use App\Models\Invoice;
use App\Notifications\InvoiceSentNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Notification;

class SendInvoiceBulkEmail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public Invoice $invoice) {}

    public function handle(): void
    {
        $notifiables = \App\Models\User::where('is_active', true)
            ->whereHas('roles', fn ($q) => $q->whereIn('name', ['owner', 'admin', 'accountant', 'sales']))
            ->get();

        if ($notifiables->isEmpty()) {
            return;
        }

        Notification::send($notifiables, new InvoiceSentNotification($this->invoice));

        $this->invoice->update(['status' => 'sent', 'sent_at' => now()]);
    }
}
