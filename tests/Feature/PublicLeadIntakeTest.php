<?php

namespace Tests\Feature;

use App\Models\LeadStatus;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicLeadIntakeTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_lead_intake_creates_lead(): void
    {
        LeadStatus::factory()->create(['is_won' => false, 'is_lost' => false, 'name' => 'New']);

        $payload = [
            'name' => 'Jane Doe',
            'email' => 'jane@example.com',
            'company' => 'Acme Inc',
            'message' => 'Want a demo.',
        ];

        $response = $this->postJson('/api/v1/public/leads', $payload);

        $response->assertSuccessful();
        $this->assertDatabaseHas('leads', ['email' => 'jane@example.com', 'name' => 'Jane Doe']);
    }

    public function test_public_lead_intake_validates_required_fields(): void
    {
        $this->postJson('/api/v1/public/leads', [])
            ->assertStatus(422);
    }
}
