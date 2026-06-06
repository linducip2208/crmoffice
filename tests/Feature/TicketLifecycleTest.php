<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\Contact;
use App\Models\Department;
use App\Models\Ticket;
use App\Models\TicketPriority;
use App\Models\TicketReply;
use App\Models\TicketStatus;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TicketLifecycleTest extends TestCase
{
    use RefreshDatabase;

    public function test_ticket_can_be_created_with_relationships(): void
    {
        $client = Client::factory()->create();
        $department = Department::factory()->create(['name' => 'Technical']);
        $priority = TicketPriority::factory()->create(['name' => 'High']);
        $status = TicketStatus::factory()->create(['name' => 'Open', 'is_open' => true]);

        $ticket = Ticket::factory()->create([
            'client_id' => $client->id,
            'department_id' => $department->id,
            'priority_id' => $priority->id,
            'status_id' => $status->id,
            'subject' => 'Cannot login to dashboard',
        ]);

        $this->assertNotNull($ticket);
        $this->assertEquals('Cannot login to dashboard', $ticket->subject);
        $this->assertEquals($client->id, $ticket->client_id);
        $this->assertEquals('High', $ticket->priority->name);
        $this->assertEquals('Open', $ticket->status->name);
    }

    public function test_ticket_can_be_assigned_to_staff(): void
    {
        $user = User::factory()->create(['name' => 'Support Agent']);
        $status = TicketStatus::factory()->create(['is_open' => true]);
        $priority = TicketPriority::factory()->create();

        $ticket = Ticket::factory()->create([
            'assigned_to' => $user->id,
            'status_id' => $status->id,
            'priority_id' => $priority->id,
        ]);

        $this->assertNotNull($ticket->assignee);
        $this->assertEquals('Support Agent', $ticket->assignee->name);
    }

    public function test_ticket_replies_by_staff(): void
    {
        $user = User::factory()->create();
        $status = TicketStatus::factory()->create(['is_open' => true]);
        $priority = TicketPriority::factory()->create();

        $ticket = Ticket::factory()->create([
            'status_id' => $status->id,
            'priority_id' => $priority->id,
        ]);

        TicketReply::create([
            'ticket_id' => $ticket->id,
            'user_id' => $user->id,
            'body' => 'We are looking into this issue.',
            'is_internal' => false,
            'source' => 'web',
        ]);

        $this->assertEquals(1, $ticket->replies()->count());
        $this->assertEquals('We are looking into this issue.', $ticket->replies()->first()->body);
    }

    public function test_ticket_replies_by_customer_contact(): void
    {
        $client = Client::factory()->create();
        $contact = Contact::factory()->create(['client_id' => $client->id]);
        $status = TicketStatus::factory()->create(['is_open' => true]);
        $priority = TicketPriority::factory()->create();

        $ticket = Ticket::factory()->create([
            'client_id' => $client->id,
            'contact_id' => $contact->id,
            'status_id' => $status->id,
            'priority_id' => $priority->id,
        ]);

        TicketReply::create([
            'ticket_id' => $ticket->id,
            'contact_id' => $contact->id,
            'body' => 'Here is the error log.',
            'is_internal' => false,
            'source' => 'portal',
        ]);

        $this->assertEquals(1, $ticket->replies()->count());
        $this->assertEquals('Here is the error log.', $ticket->replies()->first()->body);
        $this->assertEquals($contact->id, $ticket->replies()->first()->contact_id);
    }

    public function test_internal_reply_flag(): void
    {
        $user = User::factory()->create();
        $status = TicketStatus::factory()->create(['is_open' => true]);
        $priority = TicketPriority::factory()->create();

        $ticket = Ticket::factory()->create([
            'status_id' => $status->id,
            'priority_id' => $priority->id,
        ]);

        $internalReply = TicketReply::create([
            'ticket_id' => $ticket->id,
            'user_id' => $user->id,
            'body' => 'Internal note: escalate to dev team.',
            'is_internal' => true,
            'source' => 'web',
        ]);

        $publicReply = TicketReply::create([
            'ticket_id' => $ticket->id,
            'user_id' => $user->id,
            'body' => 'We have escalated this to our dev team.',
            'is_internal' => false,
            'source' => 'web',
        ]);

        $this->assertEquals(2, $ticket->replies()->count());
        $this->assertTrue((bool) $internalReply->is_internal);
        $this->assertFalse((bool) $publicReply->is_internal);
    }

    public function test_sla_due_dates_are_calculated(): void
    {
        $status = TicketStatus::factory()->create(['is_open' => true]);
        $priority = TicketPriority::factory()->create();

        $ticket = Ticket::factory()->create([
            'status_id' => $status->id,
            'priority_id' => $priority->id,
            'first_response_due_at' => now()->addHours(4),
            'resolve_due_at' => now()->addHours(24),
        ]);

        $this->assertNotNull($ticket->first_response_due_at);
        $this->assertNotNull($ticket->resolve_due_at);
        $this->assertTrue($ticket->first_response_due_at->gt(now()));
        $this->assertTrue($ticket->resolve_due_at->gt($ticket->first_response_due_at));
    }

    public function test_ticket_resolution_sets_timestamps(): void
    {
        $openStatus = TicketStatus::factory()->create(['is_open' => true]);
        $resolvedStatus = TicketStatus::factory()->create(['is_open' => false, 'name' => 'Resolved']);
        $priority = TicketPriority::factory()->create();

        $ticket = Ticket::factory()->create([
            'status_id' => $openStatus->id,
            'priority_id' => $priority->id,
            'first_response_at' => now()->subHours(2),
        ]);

        $ticket->update([
            'status_id' => $resolvedStatus->id,
            'resolved_at' => now(),
            'closed_at' => now(),
        ]);

        $this->assertNotNull($ticket->fresh()->resolved_at);
        $this->assertNotNull($ticket->fresh()->closed_at);
        $this->assertEquals($resolvedStatus->id, $ticket->fresh()->status_id);
    }
}
