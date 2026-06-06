<?php

namespace App\Actions\Sales;

use App\Models\Currency;
use App\Models\Invoice;
use App\Models\Project;
use App\Services\NumberSequence;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class InvoiceTimeEntries
{
    public function handle(Project $project, array $timeEntryIds = []): Invoice
    {
        return DB::transaction(function () use ($project, $timeEntryIds) {
            $entries = $project->timeEntries()
                ->where('is_billable', true)
                ->where('is_invoiced', false)
                ->whereNotNull('end_at')
                ->when($timeEntryIds, fn ($q) => $q->whereIn('id', $timeEntryIds))
                ->get();

            if ($entries->isEmpty()) {
                throw new \RuntimeException('No billable, unbilled time entries found.');
            }

            $invoice = Invoice::create([
                'number' => NumberSequence::next('invoice'),
                'client_id' => $project->client_id,
                'project_id' => $project->id,
                'invoice_date' => now()->toDateString(),
                'due_date' => now()->addDays(14)->toDateString(),
                'currency_id' => $project->currency_id ?? Currency::where('is_base', true)->value('id'),
                'subtotal' => 0,
                'discount_total' => 0,
                'tax_total' => 0,
                'total' => 0,
                'paid_total' => 0,
                'balance_due' => 0,
                'status' => 'draft',
                'public_token' => Str::random(40),
                'created_by' => auth()->id(),
            ]);

            foreach ($entries->groupBy('task_id') as $taskId => $taskEntries) {
                $totalMinutes = $taskEntries->sum(fn ($e) => $e->minutes ?? (int) (($e->end_at?->diffInSeconds($e->start_at) ?: 0) / 60));
                $hours = round($totalMinutes / 60, 2);
                $rate = $taskEntries->first()->hourly_rate ?? $project->hourly_rate ?? 0;
                $taskTitle = $taskEntries->first()->task?->title ?? "Task #{$taskId}";

                $item = $invoice->items()->create([
                    'description' => "Time tracking: {$taskTitle} ({$hours}h)",
                    'quantity' => $hours,
                    'unit_price' => $rate,
                    'line_total' => round($hours * $rate, 2),
                    'order' => 0,
                ]);

                $taskEntries->each(fn ($e) => $e->update(['is_invoiced' => true, 'invoice_item_id' => $item->id]));
            }

            return $invoice->fresh();
        });
    }
}
