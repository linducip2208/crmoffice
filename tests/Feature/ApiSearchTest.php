<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ApiSearchTest extends TestCase
{
    use RefreshDatabase;

    public function test_search_requires_auth(): void
    {
        $this->getJson('/api/v1/search?q=test')->assertUnauthorized();
    }

    public function test_authenticated_user_can_query_search(): void
    {
        Sanctum::actingAs(User::factory()->create());

        $this->getJson('/api/v1/search?q=test')->assertSuccessful();
    }
}
