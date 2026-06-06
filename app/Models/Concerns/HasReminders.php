<?php

namespace App\Models\Concerns;

use App\Models\CalendarEvent;
use App\Services\ReminderService;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Carbon;

trait HasReminders
{
    public function reminders(): MorphMany
    {
        return $this->morphMany(CalendarEvent::class, 'related');
    }

    public function addReminder(Carbon $at, string $message): CalendarEvent
    {
        return app(ReminderService::class)->attachToEntity($this, $at, $message);
    }
}
