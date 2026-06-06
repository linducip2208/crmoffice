<?php

use App\Actions\Crm\ConvertLeadToClient;
use App\Models\Client;
use App\Models\Contact;
use App\Models\Currency;
use App\Models\Lead;
use App\Models\LeadStatus;
use App\Models\User;

test('crm flow: create client, add contact, create lead, convert lead to client', function () {
    Currency::factory()->create(['code' => 'IDR', 'is_base' => true]);
    $user = User::factory()->create();
    $status = LeadStatus::factory()->create(['name' => 'New']);

    $client = Client::factory()->create([
        'company_name' => 'PT Maju Bersama',
        'status' => 'active',
        'account_manager_id' => $user->id,
    ]);

    expect($client->company_name)->toBe('PT Maju Bersama');
    expect($client->status)->toBe('active');

    $contact = Contact::factory()->create([
        'client_id' => $client->id,
        'email' => 'contact@majubersama.com',
        'is_primary' => true,
    ]);

    expect($contact->client_id)->toBe($client->id);
    expect($contact->is_primary)->toBeTrue();

    $lead = Lead::factory()->create([
        'name' => 'Dian Permata',
        'email' => 'dian@example.com',
        'phone' => '081234567890',
        'company' => 'PT Maju Bersama',
        'lead_status_id' => $status->id,
        'assigned_to' => $user->id,
    ]);

    $convertedClient = (new ConvertLeadToClient)->handle($lead);

    expect($convertedClient)->not->toBeNull();
    expect($convertedClient->company_name)->toBe('PT Maju Bersama');

    $this->assertDatabaseHas('contacts', [
        'email' => 'dian@example.com',
        'client_id' => $convertedClient->id,
        'is_primary' => true,
    ]);

    $lead->refresh();
    expect($lead->converted_at)->not->toBeNull();
    expect($lead->converted_to_client_id)->toBe($convertedClient->id);
});

test('crm flow: contact is properly linked to client', function () {
    Currency::factory()->create(['code' => 'USD', 'is_base' => true]);

    $client = Client::factory()->create(['company_name' => 'CV Sumber Rezeki']);

    $c1 = Contact::factory()->create([
        'client_id' => $client->id,
        'is_primary' => true,
    ]);
    $c2 = Contact::factory()->create([
        'client_id' => $client->id,
        'is_primary' => false,
    ]);

    expect($client->contacts()->count())->toBe(2);
    expect($client->contacts()->where('is_primary', true)->count())->toBe(1);
});

test('crm flow: lead without company name still converts', function () {
    Currency::factory()->create(['code' => 'IDR', 'is_base' => true]);
    $status = LeadStatus::factory()->create();

    $lead = Lead::factory()->create([
        'name' => 'Solo Entrepreneur',
        'email' => 'solo@example.com',
        'lead_status_id' => $status->id,
    ]);

    $client = (new ConvertLeadToClient)->handle($lead);

    expect($client)->not->toBeNull();
    expect($client->company_name)->not->toBeEmpty();

    $lead->refresh();
    expect($lead->converted_at)->not->toBeNull();
});
