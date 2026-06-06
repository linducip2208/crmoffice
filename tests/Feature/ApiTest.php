<?php

use App\Models\Client;
use App\Models\Invoice;
use App\Models\LeadStatus;
use App\Models\User;
use Laravel\Sanctum\Sanctum;

test('api: sanctum auth returns token', function () {
    $user = User::factory()->create([
        'email' => 'apitest@example.com',
        'password' => bcrypt('password123'),
    ]);

    $response = $this->postJson('/api/v1/auth/login', [
        'email' => 'apitest@example.com',
        'password' => 'password123',
        'device_name' => 'test-client',
    ]);

    $response->assertSuccessful();
    $response->assertJsonStructure([
        'token',
        'user' => ['id', 'name', 'email'],
    ]);
    expect($response->json('token'))->not->toBeEmpty();
});

test('api: invalid credentials returns 422', function () {
    User::factory()->create([
        'email' => 'valid@example.com',
        'password' => bcrypt('correct'),
    ]);

    $response = $this->postJson('/api/v1/auth/login', [
        'email' => 'valid@example.com',
        'password' => 'wrong',
    ]);

    $response->assertStatus(422);
    $response->assertJsonValidationErrors('email');
});

test('api: list clients requires auth', function () {
    $response = $this->getJson('/api/v1/clients');
    $response->assertStatus(401);
});

test('api: authenticated user can list clients', function () {
    Sanctum::actingAs(User::factory()->create());

    Client::factory()->count(5)->create();

    $response = $this->getJson('/api/v1/clients');

    $response->assertSuccessful();
    $response->assertJsonStructure([
        'data' => [
            '*' => ['id', 'company_name', 'status'],
        ],
    ]);
    expect(count($response->json('data')))->toBe(5);
});

test('api: authenticated user can get invoices', function () {
    Sanctum::actingAs(User::factory()->create());

    Invoice::factory()->count(3)->create();

    $response = $this->getJson('/api/v1/invoices');

    $response->assertSuccessful();
    $response->assertJsonStructure([
        'data' => [
            '*' => ['id', 'number', 'status', 'total'],
        ],
    ]);
    expect(count($response->json('data')))->toBe(3);
});

test('api: can create lead via api', function () {
    Sanctum::actingAs(User::factory()->create());

    $status = LeadStatus::factory()->create(['name' => 'New']);

    $response = $this->postJson('/api/v1/leads', [
        'name' => 'API Generated Lead',
        'email' => 'api-lead@example.com',
        'company' => 'API Corp',
        'lead_status_id' => $status->id,
    ]);

    $response->assertStatus(201);
    $response->assertJsonPath('data.name', 'API Generated Lead');

    $this->assertDatabaseHas('leads', [
        'email' => 'api-lead@example.com',
        'name' => 'API Generated Lead',
    ]);
});

test('api: me endpoint returns authenticated user', function () {
    $user = User::factory()->create([
        'name' => 'Test User',
        'email' => 'testuser@example.com',
    ]);

    Sanctum::actingAs($user);

    $response = $this->getJson('/api/v1/auth/me');

    $response->assertSuccessful();
    $response->assertJsonPath('data.name', 'Test User');
    $response->assertJsonPath('data.email', 'testuser@example.com');
});

test('api: unauthenticated request returns 401', function () {
    $response = $this->getJson('/api/v1/invoices');
    $response->assertStatus(401);
});
