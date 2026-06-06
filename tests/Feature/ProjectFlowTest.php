<?php

use App\Events\TaskAssigned;
use App\Models\Currency;
use App\Models\Milestone;
use App\Models\Project;
use App\Models\Task;
use App\Models\TimeEntry;
use App\Models\User;
use Illuminate\Support\Facades\Event;

test('project flow: create project, add milestones and tasks, assign users', function () {
    Event::fake([TaskAssigned::class]);

    Currency::factory()->create(['is_base' => true]);
    $pm = User::factory()->create(['name' => 'Project Manager']);
    $dev = User::factory()->create(['name' => 'Developer']);

    $project = Project::factory()->create([
        'name' => 'Website Redesign',
        'project_manager_id' => $pm->id,
        'status' => 'in_progress',
    ]);

    expect($project->name)->toBe('Website Redesign');
    expect($project->project_manager_id)->toBe($pm->id);

    $milestone1 = Milestone::factory()->create([
        'project_id' => $project->id,
        'name' => 'Design Phase',
    ]);
    $milestone2 = Milestone::factory()->create([
        'project_id' => $project->id,
        'name' => 'Development Phase',
    ]);

    expect($project->milestones()->count())->toBe(2);

    $task1 = Task::factory()->create([
        'project_id' => $project->id,
        'milestone_id' => $milestone1->id,
        'title' => 'Create wireframes',
        'status' => 'done',
        'created_by' => $pm->id,
    ]);

    $task2 = Task::factory()->create([
        'project_id' => $project->id,
        'milestone_id' => $milestone2->id,
        'title' => 'Build homepage',
        'status' => 'todo',
        'created_by' => $pm->id,
    ]);

    $task2->assignees()->attach($dev->id, ['assigned_at' => now()]);

    expect($task2->assignees()->count())->toBe(1);
    expect($task2->assignees()->first()->name)->toBe('Developer');
});

test('project flow: log time entries and verify billable tracking', function () {
    Event::fake([TaskAssigned::class]);

    Currency::factory()->create(['is_base' => true]);
    $user = User::factory()->create();

    $project = Project::factory()->create(['status' => 'in_progress']);
    $task = Task::factory()->create([
        'project_id' => $project->id,
        'is_billable' => true,
        'hourly_rate' => 100,
        'created_by' => $user->id,
    ]);

    TimeEntry::factory()->count(3)->create([
        'task_id' => $task->id,
        'user_id' => $user->id,
        'project_id' => $project->id,
        'is_billable' => true,
        'is_invoiced' => false,
    ]);

    TimeEntry::factory()->create([
        'task_id' => $task->id,
        'user_id' => $user->id,
        'project_id' => $project->id,
        'is_billable' => false,
        'is_invoiced' => false,
    ]);

    expect($task->timeEntries()->count())->toBe(4);

    $billableCount = $task->timeEntries()->where('is_billable', true)->count();
    expect($billableCount)->toBe(3);

    $totalMinutes = $task->timeEntries()->where('is_billable', true)->sum('minutes');
    expect($totalMinutes)->toBeGreaterThan(0);
});

test('project flow: project progress computed from task completion', function () {
    Event::fake([TaskAssigned::class]);

    Currency::factory()->create(['is_base' => true]);
    $user = User::factory()->create();

    $project = Project::factory()->create([
        'status' => 'in_progress',
        'progress_pct' => 0,
    ]);

    Task::factory()->count(4)->create([
        'project_id' => $project->id,
        'status' => 'done',
        'created_by' => $user->id,
    ]);

    Task::factory()->count(4)->create([
        'project_id' => $project->id,
        'status' => 'todo',
        'created_by' => $user->id,
    ]);

    $total = $project->tasks()->count();
    $done = $project->tasks()->where('status', 'done')->count();
    $progress = round(($done / $total) * 100, 2);

    expect($total)->toBe(8);
    expect($done)->toBe(4);
    expect($progress)->toBe(50.0);
});

test('project flow: time entry links to task, user, and project', function () {
    Event::fake([TaskAssigned::class]);

    Currency::factory()->create(['is_base' => true]);
    $user = User::factory()->create();

    $project = Project::factory()->create();
    $task = Task::factory()->create([
        'project_id' => $project->id,
        'created_by' => $user->id,
    ]);

    $entry = TimeEntry::factory()->create([
        'user_id' => $user->id,
        'task_id' => $task->id,
        'project_id' => $project->id,
        'minutes' => 90,
        'note' => 'Meeting with client',
    ]);

    expect($entry->user_id)->toBe($user->id);
    expect($entry->task_id)->toBe($task->id);
    expect($entry->project_id)->toBe($project->id);
    expect($entry->minutes)->toBe(90);
    expect($entry->note)->toBe('Meeting with client');
});

test('project flow: milestone completion tracks child tasks', function () {
    Event::fake([TaskAssigned::class]);

    Currency::factory()->create(['is_base' => true]);
    $user = User::factory()->create();

    $project = Project::factory()->create();
    $milestone = Milestone::factory()->create([
        'project_id' => $project->id,
        'name' => 'Sprint 1',
    ]);

    Task::factory()->count(5)->create([
        'project_id' => $project->id,
        'milestone_id' => $milestone->id,
        'status' => 'done',
        'created_by' => $user->id,
    ]);

    expect($milestone->tasks()->count())->toBe(5);
    expect($milestone->tasks()->where('status', 'done')->count())->toBe(5);
});
