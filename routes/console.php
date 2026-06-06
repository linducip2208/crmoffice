<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Recurring invoice generation daily at 00:30
Schedule::command('crmoffice:recurring-invoices')->dailyAt('00:30')->withoutOverlapping();

// Dunning reminders every 6 hours
Schedule::command('crmoffice:dunning-reminders')->everySixHours()->withoutOverlapping();

// SLA check every minute (Phase 5)
Schedule::command('crmoffice:sla-check')->everyMinute()->withoutOverlapping();

// Inbound email poll every 2 minutes (Phase 5)
Schedule::command('crmoffice:poll-inbound-email')->everyTwoMinutes()->withoutOverlapping();

// Sitemap rebuild hourly (Phase 6)
Schedule::command('crmoffice:rebuild-sitemap')->hourly();

// pSEO health audit weekly (Phase 6 quality gate)
Schedule::command('pseo:audit --limit=30')->weekly()->sundays()->at('03:00');

// Prune old jobs / sessions / cache nightly
Schedule::command('queue:prune-failed --hours=168')->daily();
Schedule::command('auth:clear-resets')->dailyAt('02:00');

// Sanctum token pruning (expired tokens) weekly
Schedule::command('sanctum:prune-expired --hours=72')->weekly();

// Nightly backup (mysqldump + storage tarball)
Schedule::command('crmoffice:backup')->dailyAt('01:30')->withoutOverlapping();

// Reminder notifications every minute
Schedule::command('crmoffice:send-reminders')->everyMinute()->withoutOverlapping();

// IndexNow daily submission at 02:45
Schedule::command('seo:indexnow --new')->dailyAt('02:45')->withoutOverlapping();

// Goals aggregation hourly
Schedule::command('crmoffice:aggregate-goals')->hourly()->withoutOverlapping();
