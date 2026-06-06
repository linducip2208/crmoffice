<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\Contact;
use App\Models\Department;
use App\Models\Invoice;
use App\Models\TicketPriority;
use App\Models\TicketStatus;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CustomerPortalTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware(\App\Http\Middleware\RequirePair::class);
    }

    public function test_portal_login_with_valid_credentials(): void
    {
        $contact = Contact::factory()->portalEnabled()->create([
            'email' => 'portaluser@example.com',
        ]);

        $response = $this->post('/portal/login', [
            'email' => 'portaluser@example.com',
            'password' => 'password',
        ]);

        $response->assertRedirect('/portal');
        $this->assertAuthenticatedAs($contact, 'portal');
    }

    public function test_portal_login_invalid_credentials(): void
    {
        Contact::factory()->portalEnabled()->create([
            'email' => 'real@example.com',
        ]);

        $response = $this->from('/portal/login')->post('/portal/login', [
            'email' => 'real@example.com',
            'password' => 'wrongpassword',
        ]);

        $response->assertRedirect('/portal/login');
        $response->assertSessionHasErrors('email');
    }

    public function test_portal_dashboard_returns_correct_data(): void
    {
        $client = Client::factory()->create(['company_name' => 'Acme Corp']);
        $contact = Contact::factory()->portalEnabled()->create([
            'client_id' => $client->id,
            'email' => 'dashboard@example.com',
        ]);

        Invoice::factory()->count(3)->create([
            'client_id' => $client->id,
            'status' => 'sent',
        ]);

        $response = $this->actingAs($contact, 'portal')->get('/portal');

        $response->assertSuccessful();
        $response->assertViewHas('client');
        $response->assertViewHas('invoices');
        $response->assertViewHas('projects');
        $response->assertViewHas('tickets');
    }

    public function test_portal_invoice_list_only_shows_own_invoices(): void
    {
        $clientA = Client::factory()->create(['company_name' => 'Client A']);
        $clientB = Client::factory()->create(['company_name' => 'Client B']);
        $contactA = Contact::factory()->portalEnabled()->create([
            'client_id' => $clientA->id,
            'email' => 'clienta@example.com',
        ]);

        Invoice::factory()->count(2)->create(['client_id' => $clientA->id]);
        Invoice::factory()->count(3)->create(['client_id' => $clientB->id]);

        $response = $this->actingAs($contactA, 'portal')->get('/portal/invoices');

        $response->assertSuccessful();
        $response->assertViewHas('invoices', function ($invoices) {
            return $invoices->total() === 2;
        });
    }

    public function test_portal_ticket_creation(): void
    {
        Department::factory()->create(['name' => 'Technical']);
        TicketPriority::factory()->create(['name' => 'Medium']);
        TicketStatus::factory()->create(['is_open' => true, 'name' => 'Open']);

        $client = Client::factory()->create();
        $contact = Contact::factory()->portalEnabled()->create([
            'client_id' => $client->id,
            'email' => 'ticketmaker@example.com',
        ]);

        $department = Department::first();
        $priority = TicketPriority::first();

        $response = $this->actingAs($contact, 'portal')->post('/portal/tickets', [
            'subject' => 'Need help with integration',
            'body' => 'The API is returning 500 errors intermittently.',
            'department_id' => $department->id,
            'priority_id' => $priority->id,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('tickets', [
            'subject' => 'Need help with integration',
            'client_id' => $client->id,
            'contact_id' => $contact->id,
        ]);
    }

    public function test_portal_ticket_creation_validation(): void
    {
        $contact = Contact::factory()->portalEnabled()->create([
            'email' => 'validator@example.com',
        ]);

        $response = $this->actingAs($contact, 'portal')
            ->from('/portal/tickets/create')
            ->post('/portal/tickets', [
                'subject' => '',
                'body' => '',
            ]);

        $response->assertRedirect('/portal/tickets/create');
        $response->assertSessionHasErrors(['subject', 'body', 'department_id', 'priority_id']);
    }

    public function test_portal_access_denied_for_inactive_contacts(): void
    {
        Contact::factory()->create([
            'email' => 'noaccess@example.com',
            'portal_access' => false,
        ]);

        $response = $this->from('/portal/login')->post('/portal/login', [
            'email' => 'noaccess@example.com',
            'password' => 'password',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertGuest('portal');
    }
}
