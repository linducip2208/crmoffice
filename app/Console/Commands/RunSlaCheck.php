<?php

namespace App\Console\Commands;

use App\Events\SlaBreached;
use App\Models\Ticket;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class RunSlaCheck extends Command
{
    protected $signature = 'crmoffice:sla-check';

    protected $description = 'Monitor SLA: dispatch SlaBreached events for tickets that breached their deadlines.';

    public function handle(): int
    {
        $now = now();
        $processed = collect();
        $total = 0;

        $breachedFirstResponse = Ticket::query()
            ->whereNull('first_response_at')
            ->whereNotNull('first_response_due_at')
            ->where('first_response_due_at', '<', $now)
            ->whereNull('closed_at')
            ->whereNull('sla_first_response_notified_at')
            ->get();

        $total += $this->notifyBreaches($breachedFirstResponse, 'sla_first_response_notified_at', $now, $processed);

        $breachedResolve = Ticket::query()
            ->whereNull('resolved_at')
            ->whereNotNull('resolve_due_at')
            ->where('resolve_due_at', '<', $now)
            ->whereNull('closed_at')
            ->whereNull('sla_resolve_notified_at')
            ->get();

        $total += $this->notifyBreaches($breachedResolve, 'sla_resolve_notified_at', $now, $processed);

        $this->info("Dispatched {$total} SLA breached events.");

        return self::SUCCESS;
    }

    /**
     * Tandai tiket "sudah dinotifikasi" LEBIH DULU, baru dispatch event.
     * Dengan begitu tiket yang sama tidak memicu notifikasi + webhook
     * berulang setiap menit (breach storm), walau command jalan berkali-kali.
     *
     * @param  Collection<int, Ticket>  $tickets
     * @param  Collection<int, bool>  $processed
     */
    private function notifyBreaches(Collection $tickets, string $notifiedColumn, Carbon $now, Collection $processed): int
    {
        if ($tickets->isEmpty()) {
            return 0;
        }

        Ticket::query()
            ->whereIn('id', $tickets->pluck('id'))
            ->update([$notifiedColumn => $now]);

        $dispatched = 0;

        foreach ($tickets as $ticket) {
            // Tiket yang breach first-response DAN resolve cukup 1 event per run.
            if ($processed->has($ticket->id)) {
                continue;
            }
            $processed->put($ticket->id, true);

            logger()->warning('SLA breached', [
                'ticket_id' => $ticket->id,
                'number' => $ticket->number,
            ]);

            SlaBreached::dispatch($ticket);
            $dispatched++;
        }

        return $dispatched;
    }
}
