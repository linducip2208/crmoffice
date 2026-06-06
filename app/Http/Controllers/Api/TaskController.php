<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Task;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class TaskController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $tasks = QueryBuilder::for(Task::class)
            ->allowedFilters([
                AllowedFilter::exact('project_id'),
                AllowedFilter::exact('status'),
                AllowedFilter::exact('priority'),
                AllowedFilter::partial('title'),
            ])
            ->allowedSorts(['title', 'due_date', 'priority', 'created_at', 'order'])
            ->allowedIncludes(['project', 'milestone'])
            ->paginate(min((int) $request->query('per_page', 20), 100));

        return response()->json($tasks);
    }

    public function show(Task $task): JsonResponse
    {
        $task->load(['project', 'milestone']);

        return response()->json(['data' => $task]);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'project_id' => 'nullable|exists:projects,id',
            'milestone_id' => 'nullable|exists:milestones,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'priority' => 'nullable|in:low,medium,high,urgent',
            'status' => 'nullable|string|max:40',
            'start_date' => 'nullable|date',
            'due_date' => 'nullable|date',
            'estimate_hours' => 'nullable|numeric|min:0',
            'is_billable' => 'boolean',
            'is_visible_to_customer' => 'boolean',
        ]);

        $task = Task::create($data + ['created_by' => $request->user()->id]);

        return response()->json(['data' => $task], 201);
    }

    public function update(Request $request, Task $task): JsonResponse
    {
        $data = $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'priority' => 'nullable|in:low,medium,high,urgent',
            'status' => 'nullable|string|max:40',
            'start_date' => 'nullable|date',
            'due_date' => 'nullable|date',
            'estimate_hours' => 'nullable|numeric|min:0',
            'is_billable' => 'boolean',
            'is_visible_to_customer' => 'boolean',
        ]);

        if (($data['status'] ?? null) === 'done' && ! $task->completed_at) {
            $data['completed_at'] = now();
        }

        $task->update($data);

        return response()->json(['data' => $task]);
    }

    public function destroy(Task $task): JsonResponse
    {
        $task->delete();

        return response()->json(null, 204);
    }
}
