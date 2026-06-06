<?php

namespace App\Console\Commands;

use App\Models\Goal;
use App\Models\Invoice;
use App\Models\Lead;
use App\Models\LeadStatus;
use App\Models\Project;
use Illuminate\Console\Command;

class AggregateGoals extends Command
{
    protected $signature = 'crmoffice:aggregate-goals';

    protected $description = 'Aggregate goal current values from source data';

    public function handle(): int
    {
        $goals = Goal::query()
            ->where('status', 'active')
            ->where('start_date', '<=', now())
            ->where('end_date', '>=', now())
            ->get();

        if ($goals->isEmpty()) {
            $this->info('No active goals found.');

            return self::SUCCESS;
        }

        $updated = 0;

        foreach ($goals as $goal) {
            $value = $this->computeCurrentValue($goal);

            if ($goal->current != $value) {
                $goal->update(['current' => $value]);
                $updated++;
                $this->line(" - {$goal->name}: {$goal->current} → {$value}");
            }
        }

        $this->info("Aggregated {$goals->count()} goals. {$updated} updated.");

        return self::SUCCESS;
    }

    protected function computeCurrentValue(Goal $goal): float|int
    {
        $from = $goal->start_date;
        $to = $goal->end_date;

        return match ($goal->metric) {
            'revenue' => (float) Invoice::query()
                ->where('status', 'paid')
                ->whereBetween('invoice_date', [$from, $to])
                ->sum('total'),

            'invoices_sent' => Invoice::query()
                ->where('status', 'sent')
                ->whereBetween('invoice_date', [$from, $to])
                ->count(),

            'deals_won' => Lead::query()
                ->whereBetween('converted_at', [$from, $to])
                ->whereHas('status', fn ($q) => $q->where('is_won', true))
                ->count(),

            'leads_converted' => Lead::query()
                ->whereBetween('converted_at', [$from, $to])
                ->whereNotNull('converted_at')
                ->count(),

            'project_completed' => Project::query()
                ->where('status', 'completed')
                ->whereBetween('updated_at', [$from, $to])
                ->count(),

            default => $goal->current,
        };
    }
}
