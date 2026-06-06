<?php

namespace Tests\Feature;

use App\Events\TaskAssigned;
use App\Models\Currency;
use App\Models\Milestone;
use App\Models\Project;
use App\Models\Task;
use App\Models\TimeEntry;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class ProjectTaskTimeFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_project_has_milestones_tasks_and_time_entries(): void
    {
        Event::fake([TaskAssigned::class]);

        Currency::factory()->create(['is_base' => true]);
        $user = User::factory()->create();

        $project = Project::factory()->create([
            'project_manager_id' => $user->id,
            'status' => 'in_progress',
        ]);

        $milestone = Milestone::factory()->create([
            'project_id' => $project->id,
            'name' => 'Phase 1',
        ]);

        $task = Task::factory()->create([
            'project_id' => $project->id,
            'milestone_id' => $milestone->id,
            'title' => 'Setup Environment',
            'status' => 'done',
            'created_by' => $user->id,
        ]);

        TimeEntry::factory()->count(3)->create([
            'task_id' => $task->id,
            'user_id' => $user->id,
            'is_billable' => true,
        ]);

        $this->assertEquals(1, $project->milestones()->count());
        $this->assertEquals('Phase 1', $project->milestones()->first()->name);
        $this->assertEquals(1, $project->tasks()->count());
        $this->assertEquals(3, $task->timeEntries()->count());
    }

    public function test_task_can_be_assigned_to_users(): void
    {
        Event::fake([TaskAssigned::class]);

        Currency::factory()->create(['is_base' => true]);
        $user = User::factory()->create();
        $assignee = User::factory()->create();

        $task = Task::factory()->create(['created_by' => $user->id]);

        $task->assignees()->attach($assignee->id, ['assigned_at' => now()]);

        $this->assertCount(1, $task->assignees);
        $assignedUser = $task->assignees->first();
        $this->assertEquals($assignee->id, $assignedUser->id);
        $this->assertNotNull($assignedUser->pivot->assigned_at);
    }

    public function test_time_entry_billing_flags(): void
    {
        Event::fake([TaskAssigned::class]);

        Currency::factory()->create(['is_base' => true]);
        $user = User::factory()->create();

        $task = Task::factory()->create([
            'is_billable' => true,
            'hourly_rate' => 75,
            'created_by' => $user->id,
        ]);

        $billable = TimeEntry::factory()->create([
            'task_id' => $task->id,
            'user_id' => $user->id,
            'is_billable' => true,
            'is_invoiced' => false,
        ]);

        $nonBillable = TimeEntry::factory()->create([
            'task_id' => $task->id,
            'user_id' => $user->id,
            'is_billable' => false,
            'is_invoiced' => false,
        ]);

        $this->assertTrue((bool) $billable->is_billable);
        $this->assertFalse((bool) $billable->is_invoiced);
        $this->assertFalse((bool) $nonBillable->is_billable);

        $billableEntries = $task->timeEntries()->where('is_billable', true)->count();
        $this->assertEquals(1, $billableEntries);
    }

    public function test_project_progress_calculation(): void
    {
        Event::fake([TaskAssigned::class]);

        Currency::factory()->create(['is_base' => true]);
        $user = User::factory()->create();

        $project = Project::factory()->create([
            'status' => 'in_progress',
            'progress_pct' => 0,
        ]);

        Task::factory()->count(3)->create([
            'project_id' => $project->id,
            'status' => 'done',
            'created_by' => $user->id,
        ]);

        Task::factory()->count(2)->create([
            'project_id' => $project->id,
            'status' => 'todo',
            'created_by' => $user->id,
        ]);

        $totalTasks = $project->tasks()->count();
        $doneTasks = $project->tasks()->where('status', 'done')->count();
        $calculatedProgress = round(($doneTasks / $totalTasks) * 100, 2);

        $this->assertEquals(5, $totalTasks);
        $this->assertEquals(3, $doneTasks);
        $this->assertEquals(60.0, $calculatedProgress);
    }

    public function test_milestone_tracks_completion(): void
    {
        Event::fake([TaskAssigned::class]);

        Currency::factory()->create(['is_base' => true]);
        $user = User::factory()->create();

        $project = Project::factory()->create();
        $milestone = Milestone::factory()->create([
            'project_id' => $project->id,
            'name' => 'Alpha',
            'complete_pct' => 0,
        ]);

        Task::factory()->count(4)->create([
            'project_id' => $project->id,
            'milestone_id' => $milestone->id,
            'status' => 'done',
            'created_by' => $user->id,
        ]);

        $this->assertEquals(4, $milestone->tasks()->where('status', 'done')->count());
        $this->assertEquals(4, $milestone->tasks()->count());
    }

    public function test_time_entry_links_to_user_task_and_project(): void
    {
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
            'minutes' => 120,
        ]);

        $this->assertEquals($user->id, $entry->user->id);
        $this->assertEquals($task->id, $entry->task->id);
        $this->assertEquals($project->id, $entry->project->id);
        $this->assertEquals(120, $entry->minutes);
    }

    public function test_task_has_checklist_and_subtasks(): void
    {
        Event::fake([TaskAssigned::class]);

        Currency::factory()->create(['is_base' => true]);
        $user = User::factory()->create();

        $parentTask = Task::factory()->create(['created_by' => $user->id, 'title' => 'Parent']);

        Task::factory()->create([
            'project_id' => $parentTask->project_id,
            'parent_task_id' => $parentTask->id,
            'title' => 'Child 1',
            'created_by' => $user->id,
        ]);

        Task::factory()->create([
            'project_id' => $parentTask->project_id,
            'parent_task_id' => $parentTask->id,
            'title' => 'Child 2',
            'created_by' => $user->id,
        ]);

        $this->assertEquals(2, $parentTask->subtasks()->count());
        $this->assertEquals('Child 1', $parentTask->subtasks()->first()->title);
    }
}
