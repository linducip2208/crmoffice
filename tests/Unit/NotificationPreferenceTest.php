<?php

namespace Tests\Unit;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NotificationPreferenceTest extends TestCase
{
    use RefreshDatabase;

    public function test_default_behavior_all_channels_enabled(): void
    {
        $user = User::factory()->create([
            'notification_preferences' => null,
        ]);

        $this->assertTrue($user->wantsNotification('invoice.created', 'database'));
        $this->assertTrue($user->wantsNotification('invoice.created', 'mail'));
        $this->assertTrue($user->wantsNotification('ticket.replied', 'database'));
        $this->assertTrue($user->wantsNotification('ticket.replied', 'mail'));
    }

    public function test_explicit_event_enable(): void
    {
        $user = User::factory()->create([
            'notification_preferences' => [
                'invoice.created' => [
                    'database' => true,
                    'mail' => true,
                ],
            ],
        ]);

        $this->assertTrue($user->wantsNotification('invoice.created', 'database'));
        $this->assertTrue($user->wantsNotification('invoice.created', 'mail'));
    }

    public function test_explicit_database_channel_disable(): void
    {
        $user = User::factory()->create([
            'notification_preferences' => [
                'invoice.created' => [
                    'database' => false,
                    'mail' => true,
                ],
            ],
        ]);

        $this->assertFalse($user->wantsNotification('invoice.created', 'database'));
        $this->assertTrue($user->wantsNotification('invoice.created', 'mail'));
    }

    public function test_explicit_mail_channel_disable(): void
    {
        $user = User::factory()->create([
            'notification_preferences' => [
                'ticket.replied' => [
                    'database' => true,
                    'mail' => false,
                ],
            ],
        ]);

        $this->assertTrue($user->wantsNotification('ticket.replied', 'database'));
        $this->assertFalse($user->wantsNotification('ticket.replied', 'mail'));
    }

    public function test_all_channels_disabled_for_event(): void
    {
        $user = User::factory()->create([
            'notification_preferences' => [
                'project.assigned' => [
                    'database' => false,
                    'mail' => false,
                ],
            ],
        ]);

        $this->assertFalse($user->wantsNotification('project.assigned', 'database'));
        $this->assertFalse($user->wantsNotification('project.assigned', 'mail'));
    }

    public function test_partial_preferences_fall_back_to_default_true(): void
    {
        $user = User::factory()->create([
            'notification_preferences' => [
                'invoice.created' => [
                    'database' => true,
                ],
            ],
        ]);

        $this->assertTrue($user->wantsNotification('invoice.created', 'database'));
        $this->assertTrue($user->wantsNotification('invoice.created', 'mail'));
    }

    public function test_unconfigured_event_falls_back_to_opt_out_default(): void
    {
        $user = User::factory()->create([
            'notification_preferences' => [
                'invoice.created' => [
                    'database' => false,
                ],
            ],
        ]);

        $this->assertTrue($user->wantsNotification('ticket.replied', 'database'));
        $this->assertTrue($user->wantsNotification('ticket.replied', 'mail'));
        $this->assertTrue($user->wantsNotification('project.assigned', 'database'));
    }

    public function test_mixed_preferences_across_events(): void
    {
        $user = User::factory()->create([
            'notification_preferences' => [
                'invoice.created' => [
                    'database' => true,
                    'mail' => false,
                ],
                'ticket.replied' => [
                    'database' => false,
                    'mail' => true,
                ],
                'project.assigned' => [
                    'database' => false,
                    'mail' => false,
                ],
            ],
        ]);

        $this->assertTrue($user->wantsNotification('invoice.created', 'database'));
        $this->assertFalse($user->wantsNotification('invoice.created', 'mail'));

        $this->assertFalse($user->wantsNotification('ticket.replied', 'database'));
        $this->assertTrue($user->wantsNotification('ticket.replied', 'mail'));

        $this->assertFalse($user->wantsNotification('project.assigned', 'database'));
        $this->assertFalse($user->wantsNotification('project.assigned', 'mail'));

        $this->assertTrue($user->wantsNotification('invoice.overdue', 'database'));
    }
}
