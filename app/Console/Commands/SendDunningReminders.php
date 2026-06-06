<?php

namespace App\Console\Commands;

use App\Events\InvoiceOverdue;
use App\Models\Invoice;
use App\Services\AiReminderMessageService;
use Illuminate\Console\Command;

class SendDunningReminders extends Command
{
    protected $signature = 'crmoffice:dunning-reminders';

    protected $description = 'Send overdue reminders for invoices past due date that have not been reminded in the last 3 days.';

    public function handle(AiReminderMessageService $reminderService): int
    {
        $cutoff = now()->subDays(3);
        $now = now();

        $invoices = Invoice::query()
            ->whereIn('status', ['sent', 'partial', 'overdue'])
            ->where('balance_due', '>', 0)
            ->where('due_date', '<', $now)
            ->where(function ($q) use ($cutoff) {
                $q->whereNull('last_reminded_at')
                    ->orWhere('last_reminded_at', '<', $cutoff);
            })
            ->get();

        if ($invoices->isEmpty()) {
            $this->info('No invoices require dunning reminders.');

            return self::SUCCESS;
        }

        $count = 0;
        foreach ($invoices as $invoice) {
            $daysOverdue = (int) $invoice->due_date->diffInDays(now());
            $tone = match (true) {
                $daysOverdue >= 30 => 'urgent',
                $daysOverdue >= 8 => 'firm',
                default => 'friendly',
            };

            $message = $reminderService->generateReminder($invoice, $tone);

            event(new InvoiceOverdue($invoice));
            $invoice->updateQuietly(['last_reminded_at' => $now]);
            $count++;

            $this->line("  [{$tone}] #{$invoice->number} — {$invoice->client?->company_name} ({$daysOverdue} days overdue)");
        }

        $this->info("Dispatched InvoiceOverdue event for $count invoices.");

        return self::SUCCESS;
    }
}
