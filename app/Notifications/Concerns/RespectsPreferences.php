<?php

namespace App\Notifications\Concerns;

/**
 * Notifications mix this in to filter channels via the recipient's
 * notification_preferences. Each notification declares its event key
 * via the abstract eventKey() method.
 */
trait RespectsPreferences
{
    abstract public function eventKey(): string;

    public function via(object $notifiable): array
    {
        $defaults = ['database', 'mail'];

        if (! method_exists($notifiable, 'wantsNotification')) {
            return $defaults;
        }

        return array_values(array_filter(
            $defaults,
            fn (string $channel) => $notifiable->wantsNotification($this->eventKey(), $channel)
        ));
    }
}
