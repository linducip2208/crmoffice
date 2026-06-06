<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AdminPanelLoginTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_login_page_renders(): void
    {
        $this->get('/admin/login')->assertOk();
    }

    public function test_authenticated_owner_can_load_dashboard(): void
    {
        Role::create(['name' => 'owner', 'guard_name' => 'web']);

        $owner = User::factory()->create([
            'is_active' => true,
            'two_factor_secret' => null,
        ]);
        $owner->assignRole('owner');

        $this->actingAs($owner)
            ->get('/admin')
            ->assertSuccessful();
    }
}
