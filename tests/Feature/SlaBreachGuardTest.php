<?php

namespace Tests\Feature;

use App\Events\SlaBreached;
use App\Models\Ticket;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class SlaBreachGuardTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware(\App\Http\Middleware\RequirePair::class);
    }

    public function test_first_response_breach_notification_is_sent_only_once(): void
    {
        Event::fake([SlaBreached::class]);

        $ticket = Ticket::factory()->create([
            'first_response_at' => null,
            'first_response_due_at' => now()->subHour(),
            'resolve_due_at' => null,
            'closed_at' => null,
        ]);

        $this->artisan('crmoffice:sla-check')->assertSuccessful();
        Event::assertDispatchedTimes(SlaBreached::class, 1);

        // Run lagi: tiket sudah ditandai → tidak boleh dispatch ulang.
        $this->artisan('crmoffice:sla-check')->assertSuccessful();
        Event::assertDispatchedTimes(SlaBreached::class, 1);

        $this->assertNotNull($ticket->fresh()->sla_first_response_notified_at);
    }

    public function test_resolve_breach_notification_is_sent_only_once(): void
    {
        Event::fake([SlaBreached::class]);

        $ticket = Ticket::factory()->create([
            'first_response_at' => now(),
            'first_response_due_at' => now()->subHours(2),
            'resolved_at' => null,
            'resolve_due_at' => now()->subHour(),
            'closed_at' => null,
        ]);

        $this->artisan('crmoffice:sla-check')->assertSuccessful();
        Event::assertDispatchedTimes(SlaBreached::class, 1);

        $this->artisan('crmoffice:sla-check')->assertSuccessful();
        Event::assertDispatchedTimes(SlaBreached::class, 1);

        $this->assertNotNull($ticket->fresh()->sla_resolve_notified_at);
    }

    public function test_ticket_that_breaches_both_deadlines_dispatches_one_event(): void
    {
        Event::fake([SlaBreached::class]);

        Ticket::factory()->create([
            'first_response_at' => null,
            'first_response_due_at' => now()->subHours(2),
            'resolved_at' => null,
            'resolve_due_at' => now()->subHour(),
            'closed_at' => null,
        ]);

        $this->artisan('crmoffice:sla-check')->assertSuccessful();

        Event::assertDispatchedTimes(SlaBreached::class, 1);
    }
}
