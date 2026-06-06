<?php

namespace App\Console\Commands;

use App\Models\Invoice;
use App\Services\NumberSequence;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

class GenerateRecurringInvoices extends Command
{
    protected $signature = 'crmoffice:recurring-invoices {--dry-run : Show what would be generated without creating}';

    protected $description = 'Generate child invoices from recurring parents whose next_recurring_date is due.';

    public function handle(): int
    {
        $today = Carbon::today();
        $parents = Invoice::query()
            ->where('is_recurring', true)
            ->whereNotNull('next_recurring_date')
            ->where('next_recurring_date', '<=', $today)
            ->where(function ($q) {
                $q->whereNull('recurring_remaining')->orWhere('recurring_remaining', '>', 0);
            })
            ->get();

        if ($parents->isEmpty()) {
            $this->info('No recurring invoices due today.');

            return self::SUCCESS;
        }

        $count = 0;
        foreach ($parents as $parent) {
            if ($this->option('dry-run')) {
                $this->line("DRY: would generate child for {$parent->number} (next: {$parent->next_recurring_date->format('Y-m-d')})");

                continue;
            }

            $child = $parent->replicate(['paid_total', 'balance_due', 'sent_at', 'viewed_at', 'pdf_file_id']);
            $child->number = NumberSequence::next('invoice');
            $child->recurring_parent_id = $parent->id;
            $child->is_recurring = false;
            $child->recurring_period = null;
            $child->recurring_count = null;
            $child->recurring_remaining = null;
            $child->next_recurring_date = null;
            $child->invoice_date = $today->toDateString();
            $child->due_date = $today->copy()->addDays((int) ($parent->due_date->diffInDays($parent->invoice_date) ?: 14))->toDateString();
            $child->status = 'draft';
            $child->paid_total = 0;
            $child->balance_due = $parent->total;
            $child->public_token = Str::random(40);
            $child->sent_at = null;
            $child->viewed_at = null;
            $child->save();

            foreach ($parent->items as $item) {
                $child->items()->create($item->only([
                    'item_id', 'description', 'quantity', 'unit_price',
                    'tax_rate_id', 'discount_pct', 'line_total', 'order',
                ]));
            }

            // Advance parent's next_recurring_date
            $next = match ($parent->recurring_period) {
                'daily' => $parent->next_recurring_date->copy()->addDay(),
                'weekly' => $parent->next_recurring_date->copy()->addWeek(),
                'monthly' => $parent->next_recurring_date->copy()->addMonth(),
                'quarterly' => $parent->next_recurring_date->copy()->addQuarter(),
                'yearly' => $parent->next_recurring_date->copy()->addYear(),
                default => $parent->next_recurring_date->copy()->addMonth(),
            };
            $remaining = $parent->recurring_remaining !== null ? max(0, $parent->recurring_remaining - 1) : null;
            $parent->update([
                'next_recurring_date' => $remaining === 0 ? null : $next,
                'recurring_remaining' => $remaining,
            ]);

            $count++;
            $this->line("✓ Generated {$child->number} from {$parent->number}");
        }

        $this->info("Generated $count recurring invoices.");

        return self::SUCCESS;
    }
}
