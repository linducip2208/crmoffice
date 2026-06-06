<?php

namespace App\Console\Commands;

use App\Events\SlaBreached;
use App\Models\Ticket;
use Illuminate\Console\Command;

class RunSlaCheck extends Command
{
    protected $signature = 'crmoffice:sla-check';

    protected $description = 'Monitor SLA: dispatch SlaBreached events for tickets that breached their deadlines.';

    public function handle(): int
    {
        $now = now();

        $breachedFirstResponse = Ticket::query()
            ->whereNull('first_response_at')
            ->whereNotNull('first_response_due_at')
            ->where('first_response_due_at', '<', $now)
            ->whereNull('closed_at')
            ->get();

        $breachedResolve = Ticket::query()
            ->whereNull('resolved_at')
            ->whereNotNull('resolve_due_at')
            ->where('resolve_due_at', '<', $now)
            ->whereNull('closed_at')
            ->get();

        $processed = collect();

        $total = 0;
        foreach ($breachedFirstResponse as $ticket) {
            $this->dispatchBreach($ticket, $processed);
            $total++;
        }
        foreach ($breachedResolve as $ticket) {
            $this->dispatchBreach($ticket, $processed);
            $total++;
        }

        $this->info("Dispatched {$total} SLA breached events.");

        return self::SUCCESS;
    }

    private function dispatchBreach(Ticket $ticket, $processed): void
    {
        if ($processed->has($ticket->id)) {
            return;
        }
        $processed->put($ticket->id, true);

        logger()->warning('SLA breached', [
            'ticket_id' => $ticket->id,
            'number' => $ticket->number,
        ]);

        SlaBreached::dispatch($ticket);
    }
}
