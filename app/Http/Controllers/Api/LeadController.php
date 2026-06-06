<?php

namespace App\Http\Controllers\Api;

use App\Actions\Crm\ConvertLeadToClient;
use App\Http\Controllers\Controller;
use App\Models\Lead;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class LeadController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $leads = QueryBuilder::for(Lead::class)
            ->allowedFilters([
                AllowedFilter::partial('name'),
                AllowedFilter::partial('email'),
                AllowedFilter::exact('lead_status_id'),
                AllowedFilter::exact('lead_source_id'),
                AllowedFilter::exact('assigned_to'),
            ])
            ->allowedSorts(['name', 'created_at', 'last_activity_at', 'estimated_value'])
            ->allowedIncludes(['status', 'source', 'assignee'])
            ->paginate(min((int) $request->query('per_page', 20), 100));

        return response()->json($leads);
    }

    public function show(Lead $lead): JsonResponse
    {
        return response()->json(['data' => $lead]);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name' => 'required|string|max:180',
            'company' => 'nullable|string|max:255',
            'email' => 'nullable|email',
            'phone' => 'nullable|string|max:40',
            'website' => 'nullable|url',
            'lead_source_id' => 'nullable|exists:lead_sources,id',
            'lead_status_id' => 'required|exists:lead_statuses,id',
            'assigned_to' => 'nullable|exists:users,id',
            'estimated_value' => 'nullable|numeric|min:0',
            'expected_close' => 'nullable|date',
            'description' => 'nullable|string',
        ]);

        $lead = Lead::create($data + ['last_activity_at' => now()]);

        return response()->json(['data' => $lead], 201);
    }

    public function update(Request $request, Lead $lead): JsonResponse
    {
        $data = $request->validate([
            'name' => 'sometimes|required|string|max:180',
            'company' => 'nullable|string|max:255',
            'email' => 'nullable|email',
            'phone' => 'nullable|string|max:40',
            'website' => 'nullable|url',
            'lead_source_id' => 'nullable|exists:lead_sources,id',
            'lead_status_id' => 'sometimes|required|exists:lead_statuses,id',
            'assigned_to' => 'nullable|exists:users,id',
            'estimated_value' => 'nullable|numeric|min:0',
            'expected_close' => 'nullable|date',
            'description' => 'nullable|string',
        ]);

        $lead->update($data + ['last_activity_at' => now()]);

        return response()->json(['data' => $lead]);
    }

    public function destroy(Lead $lead): JsonResponse
    {
        $lead->delete();

        return response()->json(null, 204);
    }

    public function convert(Lead $lead, ConvertLeadToClient $action): JsonResponse
    {
        $client = $action->handle($lead);

        return response()->json(['data' => $client], 201);
    }
}
