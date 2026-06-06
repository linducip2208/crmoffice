<?php

namespace Tests\Feature;

use Tests\TestCase;

class HealthCheckTest extends TestCase
{
    public function test_healthz_returns_ok(): void
    {
        $this->get('/healthz')->assertOk();
    }

    public function test_api_health_returns_payload(): void
    {
        $this->getJson('/api/v1/health')
            ->assertOk()
            ->assertJson(['ok' => true, 'app' => 'crmoffice']);
    }
}
