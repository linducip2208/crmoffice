<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Project;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class ProjectController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $projects = QueryBuilder::for(Project::class)
            ->allowedFilters([
                AllowedFilter::exact('client_id'),
                AllowedFilter::exact('status'),
                AllowedFilter::exact('project_manager_id'),
                AllowedFilter::partial('name'),
            ])
            ->allowedSorts(['name', 'start_date', 'deadline', 'created_at'])
            ->allowedIncludes(['client', 'manager', 'milestones'])
            ->paginate(min((int) $request->query('per_page', 20), 100));

        return response()->json($projects);
    }

    public function show(Project $project): JsonResponse
    {
        $project->load(['client', 'milestones']);

        return response()->json(['data' => $project]);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'client_id' => 'required|exists:clients,id',
            'project_manager_id' => 'nullable|exists:users,id',
            'start_date' => 'nullable|date',
            'deadline' => 'nullable|date|after_or_equal:start_date',
            'billing_method' => 'nullable|in:fixed,hourly,milestone,non_billable',
            'fixed_price' => 'nullable|numeric|min:0',
            'hourly_rate' => 'nullable|numeric|min:0',
            'currency_id' => 'required|exists:currencies,id',
            'is_visible_to_customer' => 'boolean',
        ]);

        $project = Project::create($data);

        return response()->json(['data' => $project], 201);
    }

    public function update(Request $request, Project $project): JsonResponse
    {
        $data = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'project_manager_id' => 'nullable|exists:users,id',
            'start_date' => 'nullable|date',
            'deadline' => 'nullable|date',
            'status' => 'nullable|in:not_started,in_progress,completed,on_hold,cancelled',
            'progress_pct' => 'nullable|numeric|between:0,100',
            'is_visible_to_customer' => 'boolean',
        ]);

        $project->update($data);

        return response()->json(['data' => $project]);
    }

    public function destroy(Project $project): JsonResponse
    {
        $project->delete();

        return response()->json(null, 204);
    }
}
