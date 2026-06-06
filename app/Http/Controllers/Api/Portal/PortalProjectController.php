<?php

namespace App\Http\Controllers\Api\Portal;

use App\Http\Controllers\Controller;
use App\Models\Project;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PortalProjectController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $projects = Project::query()
            ->where('client_id', $request->user()->client_id)
            ->where('is_visible_to_customer', true)
            ->orderByDesc('updated_at')
            ->paginate(20);

        return response()->json($projects);
    }

    public function show(Request $request, Project $project): JsonResponse
    {
        if ($project->client_id !== $request->user()->client_id || ! $project->is_visible_to_customer) {
            abort(404);
        }

        $project->load([
            'milestones',
            'tasks' => fn ($q) => $q->where('is_visible_to_customer', true),
        ]);

        return response()->json(['data' => $project]);
    }
}
