<?php

namespace Tests\Feature;

use App\Actions\Crm\ConvertLeadToClient;
use App\Models\Client;
use App\Models\Currency;
use App\Models\Lead;
use App\Models\LeadStatus;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LeadToClientConversionTest extends TestCase
{
    use RefreshDatabase;

    public function test_lead_converts_to_client_with_primary_contact(): void
    {
        Currency::factory()->create(['code' => 'IDR', 'is_base' => true]);
        $status = LeadStatus::factory()->create(['name' => 'Qualified']);
        $user = User::factory()->create();

        $lead = Lead::factory()->create([
            'name' => 'Budi Santoso',
            'email' => 'budi@example.com',
            'phone' => '081234567890',
            'company' => 'PT Maju Jaya',
            'lead_status_id' => $status->id,
            'assigned_to' => $user->id,
        ]);

        $client = (new ConvertLeadToClient)->handle($lead);

        $this->assertNotNull($client);
        $this->assertEquals('PT Maju Jaya', $client->company_name);
        $this->assertDatabaseHas('contacts', [
            'email' => 'budi@example.com',
            'client_id' => $client->id,
            'is_primary' => true,
        ]);

        $lead->refresh();
        $this->assertNotNull($lead->converted_at);
        $this->assertEquals($client->id, $lead->converted_to_client_id);
    }

    public function test_already_converted_lead_returns_existing_client(): void
    {
        Currency::factory()->create(['code' => 'USD', 'is_base' => true]);
        $status = LeadStatus::factory()->create();

        $lead = Lead::factory()->create([
            'name' => 'Already Converted',
            'email' => 'already@example.com',
            'lead_status_id' => $status->id,
        ]);

        $first = (new ConvertLeadToClient)->handle($lead);
        $second = (new ConvertLeadToClient)->handle($lead);

        $this->assertEquals($first->id, $second->id);
        $this->assertEquals(1, Client::count());
    }

    public function test_lead_without_email_still_creates_client(): void
    {
        Currency::factory()->create(['code' => 'USD', 'is_base' => true]);
        $status = LeadStatus::factory()->create();

        $lead = Lead::factory()->create([
            'name' => 'No Email Lead',
            'email' => null,
            'phone' => null,
            'company' => 'NoContact Co',
            'lead_status_id' => $status->id,
        ]);

        $client = (new ConvertLeadToClient)->handle($lead);

        $this->assertNotNull($client);
        $this->assertEquals('NoContact Co', $client->company_name);
        $this->assertEquals(0, $client->contacts()->count());

        $lead->refresh();
        $this->assertNotNull($lead->converted_at);
    }

    public function test_conversion_preserves_custom_fields(): void
    {
        Currency::factory()->create(['code' => 'EUR', 'is_base' => true]);
        $status = LeadStatus::factory()->create();

        $lead = Lead::factory()->create([
            'name' => 'Custom Fields',
            'email' => 'custom@example.com',
            'company' => 'Custom Co',
            'lead_status_id' => $status->id,
            'custom_fields' => ['source' => 'referral', 'tier' => 'gold'],
        ]);

        $client = (new ConvertLeadToClient)->handle($lead);

        $this->assertEquals(['source' => 'referral', 'tier' => 'gold'], $client->custom_fields);
    }

    public function test_conversion_with_overrides(): void
    {
        Currency::factory()->create(['code' => 'GBP', 'is_base' => true]);
        $status = LeadStatus::factory()->create();

        $lead = Lead::factory()->create([
            'name' => 'Override Lead',
            'email' => 'override@example.com',
            'company' => 'Original Co',
            'phone' => '111111',
            'lead_status_id' => $status->id,
        ]);

        $client = (new ConvertLeadToClient)->handle($lead, [
            'company_name' => 'Renamed Co',
            'phone' => '999999',
        ]);

        $this->assertEquals('Renamed Co', $client->company_name);
        $this->assertEquals('999999', $client->phone);
    }

    public function test_conversion_assigns_account_manager_from_lead(): void
    {
        Currency::factory()->create(['code' => 'USD', 'is_base' => true]);
        $status = LeadStatus::factory()->create();
        $manager = User::factory()->create(['name' => 'Sales Manager']);

        $lead = Lead::factory()->create([
            'name' => 'Account Manager Test',
            'email' => 'am@example.com',
            'company' => 'AM Co',
            'lead_status_id' => $status->id,
            'assigned_to' => $manager->id,
        ]);

        $client = (new ConvertLeadToClient)->handle($lead);

        $this->assertEquals($manager->id, $client->account_manager_id);

        $contact = $client->contacts()->first();
        $this->assertEquals('Account Manager Test', $contact->first_name);
        $this->assertTrue($contact->is_primary);
    }
}
