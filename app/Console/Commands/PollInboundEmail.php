<?php

namespace App\Console\Commands;

use App\Models\Department;
use Illuminate\Console\Command;

class PollInboundEmail extends Command
{
    protected $signature = 'crmoffice:poll-inbound-email';

    protected $description = 'Poll IMAP inboxes for each department and pipe new mail to tickets.';

    public function handle(): int
    {
        // Stub implementation — real IMAP polling requires webklex/laravel-imap configured per department.
        // For each Department where email_pipe is set and provider settings exist:
        //   1. Connect via IMAP/OAuth
        //   2. Fetch UNSEEN
        //   3. Parse subject for [#TICKET-NUMBER] → append reply
        //   4. Else create new Ticket with email_from
        //   5. Mark message as seen
        $count = Department::query()
            ->where('is_active', true)
            ->whereNotNull('email_pipe')
            ->count();

        $this->info("Polling stub: {$count} departments have email_pipe configured. Implement IMAP fetch when mail provider is added.");

        return self::SUCCESS;
    }
}
