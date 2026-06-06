<?php

namespace App\Console\Commands;

use App\Services\ReminderService;
use Illuminate\Console\Command;

class SendReminders extends Command
{
    protected $signature = 'crmoffice:send-reminders';

    protected $description = 'Send due reminders from CalendarEvent records as notifications.';

    public function handle(ReminderService $service): int
    {
        $service->sendDueReminders();

        $this->info('Due reminders processed.');

        return self::SUCCESS;
    }
}
