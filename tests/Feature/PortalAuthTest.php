<?php

namespace Tests\Feature;

use App\Models\Contact;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PortalAuthTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware(\App\Http\Middleware\RequirePair::class);
    }

    public function test_login_page_renders(): void
    {
        $this->get('/portal/login')->assertSuccessful();
    }

    public function test_contact_with_portal_access_can_login(): void
    {
        $contact = Contact::factory()->portalEnabled()->create([
            'email' => 'cust@example.com',
        ]);

        $this->post('/portal/login', [
            'email' => 'cust@example.com',
            'password' => 'password',
        ])->assertRedirect('/portal');

        $this->assertAuthenticatedAs($contact, 'portal');
    }

    public function test_contact_without_portal_access_cannot_login(): void
    {
        Contact::factory()->create([
            'email' => 'noaccess@example.com',
            'portal_access' => false,
        ]);

        $this->from('/portal/login')->post('/portal/login', [
            'email' => 'noaccess@example.com',
            'password' => 'password',
        ])->assertSessionHasErrors('email');
    }
}
