<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Client;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class ClientController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $clients = QueryBuilder::for(Client::class)
            ->allowedFilters([
                AllowedFilter::partial('company_name'),
                AllowedFilter::exact('status'),
                AllowedFilter::exact('account_manager_id'),
                AllowedFilter::exact('industry'),
            ])
            ->allowedSorts(['company_name', 'created_at', 'updated_at'])
            ->allowedIncludes(['primaryContact', 'contacts'])
            ->paginate(min((int) $request->query('per_page', 20), 100));

        return response()->json($clients);
    }

    public function show(Client $client): JsonResponse
    {
        $client->load(['contacts', 'primaryContact', 'defaultCurrency', 'accountManager']);

        return response()->json(['data' => $client]);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'company_name' => 'required|string|max:255',
            'industry' => 'nullable|string|max:120',
            'website' => 'nullable|url',
            'phone' => 'nullable|string|max:40',
            'billing_address' => 'nullable|string',
            'billing_city' => 'nullable|string|max:120',
            'billing_country' => 'nullable|string|size:2',
            'tax_id' => 'nullable|string|max:60',
            'account_manager_id' => 'nullable|exists:users,id',
            'default_currency_id' => 'required|exists:currencies,id',
            'default_language' => 'nullable|string|max:10',
            'status' => 'nullable|in:active,inactive,prospect',
            'notes' => 'nullable|string',
        ]);

        $client = Client::create($data);

        return response()->json(['data' => $client], 201);
    }

    public function update(Request $request, Client $client): JsonResponse
    {
        $data = $request->validate([
            'company_name' => 'sometimes|required|string|max:255',
            'industry' => 'nullable|string|max:120',
            'website' => 'nullable|url',
            'phone' => 'nullable|string|max:40',
            'billing_address' => 'nullable|string',
            'billing_city' => 'nullable|string|max:120',
            'billing_country' => 'nullable|string|size:2',
            'tax_id' => 'nullable|string|max:60',
            'account_manager_id' => 'nullable|exists:users,id',
            'default_currency_id' => 'sometimes|required|exists:currencies,id',
            'default_language' => 'nullable|string|max:10',
            'status' => 'nullable|in:active,inactive,prospect',
            'notes' => 'nullable|string',
        ]);

        $client->update($data);

        return response()->json(['data' => $client]);
    }

    public function destroy(Client $client): JsonResponse
    {
        $client->delete();

        return response()->json(null, 204);
    }
}
