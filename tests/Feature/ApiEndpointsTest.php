<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\Currency;
use App\Models\Invoice;
use App\Models\LeadStatus;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ApiEndpointsTest extends TestCase
{
    use RefreshDatabase;

    public function test_api_auth_token_generation(): void
    {
        $user = User::factory()->create([
            'email' => 'api@example.com',
            'password' => bcrypt('secret123'),
        ]);

        $response = $this->postJson('/api/v1/auth/login', [
            'email' => 'api@example.com',
            'password' => 'secret123',
            'device_name' => 'test-device',
        ]);

        $response->assertSuccessful();
        $response->assertJsonStructure([
            'token',
            'user' => ['id', 'name', 'email'],
        ]);
        $this->assertNotEmpty($response->json('token'));
    }

    public function test_api_auth_invalid_credentials(): void
    {
        $user = User::factory()->create([
            'email' => 'real@example.com',
            'password' => bcrypt('correct'),
        ]);

        $response = $this->postJson('/api/v1/auth/login', [
            'email' => 'real@example.com',
            'password' => 'wrong',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('email');
    }

    public function test_get_clients_returns_data(): void
    {
        Sanctum::actingAs(User::factory()->create());

        Client::factory()->count(3)->create();

        $response = $this->getJson('/api/v1/clients');

        $response->assertSuccessful();
        $response->assertJsonStructure([
            'data' => [
                '*' => ['id', 'company_name', 'status'],
            ],
        ]);
        $this->assertCount(3, $response->json('data'));
    }

    public function test_get_invoices_returns_data(): void
    {
        Sanctum::actingAs(User::factory()->create());

        Invoice::factory()->count(4)->create();

        $response = $this->getJson('/api/v1/invoices');

        $response->assertSuccessful();
        $response->assertJsonStructure([
            'data' => [
                '*' => ['id', 'number', 'status', 'total'],
            ],
        ]);
        $this->assertCount(4, $response->json('data'));
    }

    public function test_post_leads_creates_lead(): void
    {
        Sanctum::actingAs(User::factory()->create());

        $status = LeadStatus::factory()->create(['name' => 'New']);

        $response = $this->postJson('/api/v1/leads', [
            'name' => 'API Lead',
            'email' => 'apilead@example.com',
            'company' => 'API Corp',
            'lead_status_id' => $status->id,
        ]);

        $response->assertStatus(201);
        $response->assertJsonPath('data.name', 'API Lead');
        $this->assertDatabaseHas('leads', [
            'email' => 'apilead@example.com',
            'name' => 'API Lead',
        ]);
    }

    public function test_post_leads_validation(): void
    {
        Sanctum::actingAs(User::factory()->create());

        $response = $this->postJson('/api/v1/leads', [
            'name' => '',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['name', 'lead_status_id']);
    }

    public function test_api_returns_401_without_token(): void
    {
        $response = $this->getJson('/api/v1/clients');

        $response->assertStatus(401);
    }

    public function test_api_me_returns_authenticated_user(): void
    {
        $user = User::factory()->create([
            'name' => 'API User',
            'email' => 'me@example.com',
        ]);

        Sanctum::actingAs($user);

        $response = $this->getJson('/api/v1/auth/me');

        $response->assertSuccessful();
        $response->assertJsonPath('data.name', 'API User');
        $response->assertJsonPath('data.email', 'me@example.com');
    }
}
