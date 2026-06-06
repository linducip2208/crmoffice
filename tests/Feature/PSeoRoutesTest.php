<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PSeoRoutesTest extends TestCase
{
    use RefreshDatabase;

    public function test_best_crm_for_industry_renders(): void
    {
        $this->withoutMiddleware(\App\Http\Middleware\RequirePair::class)
            ->get('/best-crm-for-agencies')
            ->assertOk();
    }

    public function test_alternatives_to_renders(): void
    {
        $this->withoutMiddleware(\App\Http\Middleware\RequirePair::class)
            ->get('/alternatives-to-perfex')
            ->assertOk();
    }

    public function test_compare_renders(): void
    {
        $this->withoutMiddleware(\App\Http\Middleware\RequirePair::class)
            ->get('/compare/crmoffice-vs-perfex')
            ->assertOk();
    }

    public function test_sitemap_renders(): void
    {
        $this->get('/sitemap.xml')->assertOk();
    }
}
