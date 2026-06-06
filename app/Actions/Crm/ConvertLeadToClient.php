<?php

namespace App\Actions\Crm;

use App\Models\Client;
use App\Models\Currency;
use App\Models\Lead;
use Illuminate\Support\Facades\DB;

class ConvertLeadToClient
{
    public function handle(Lead $lead, array $overrides = []): Client
    {
        return DB::transaction(function () use ($lead, $overrides) {
            if ($lead->converted_to_client_id) {
                return $lead->convertedClient;
            }

            $client = Client::create(array_merge([
                'company_name' => $overrides['company_name'] ?? ($lead->company ?: $lead->name),
                'phone' => $overrides['phone'] ?? $lead->phone,
                'website' => $overrides['website'] ?? $lead->website,
                'billing_address' => $overrides['billing_address'] ?? $lead->address,
                'billing_city' => $overrides['billing_city'] ?? $lead->city,
                'billing_country' => $overrides['billing_country'] ?? $lead->country,
                'account_manager_id' => $overrides['account_manager_id'] ?? $lead->assigned_to,
                'default_currency_id' => $overrides['default_currency_id']
                    ?? $lead->currency_id
                    ?? Currency::where('is_base', true)->value('id'),
                'status' => 'active',
                'custom_fields' => $lead->custom_fields,
            ], $overrides));

            if ($lead->email || $lead->phone) {
                $client->contacts()->create([
                    'first_name' => $lead->name ?: 'Primary',
                    'email' => $lead->email,
                    'phone' => $lead->phone,
                    'is_primary' => true,
                    'portal_access' => false,
                ]);
            }

            $lead->update([
                'converted_at' => now(),
                'converted_to_client_id' => $client->id,
            ]);

            return $client;
        });
    }
}
