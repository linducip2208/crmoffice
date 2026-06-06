<?php

namespace Tests\Feature;

use App\Actions\Crm\ConvertLeadToClient;
use App\Models\Currency;
use App\Models\Lead;
use App\Models\LeadStatus;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ConvertLeadToClientTest extends TestCase
{
    use RefreshDatabase;

    public function test_lead_converts_to_client_with_primary_contact(): void
    {
        Currency::factory()->create(['code' => 'USD', 'is_base' => true]);
        $status = LeadStatus::factory()->create();

        $lead = Lead::factory()->create([
            'name' => 'Pat Customer',
            'email' => 'pat@example.com',
            'company' => 'Pat Co',
            'lead_status_id' => $status->id,
        ]);

        $client = (new ConvertLeadToClient)->handle($lead);

        $this->assertNotNull($client);
        $this->assertEquals('Pat Co', $client->company_name);
        $this->assertTrue($client->contacts()->where('email', 'pat@example.com')->exists());

        $lead->refresh();
        $this->assertNotNull($lead->converted_at);
        $this->assertEquals($client->id, $lead->converted_to_client_id);
    }
}
