<?php

namespace App\Filament\Admin\Pages;

use App\Models\Project;
use App\Models\Task;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

class TaskGantt extends Page
{
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-chart-bar';
    protected static ?string $navigationLabel = 'Gantt Chart';
    protected static string|\UnitEnum|null $navigationGroup = 'Proyek';
    protected static ?int $navigationSort = 6;
    protected static ?string $title = 'Task Gantt Chart';

    protected string $view = 'filament.admin.pages.task-gantt';

    public array $projects = [];
    public array $tasks = [];
    public int $totalProjects = 0;

    public function mount(): void
    {
        $this->totalProjects = Project::query()
            ->whereIn('status', ['not_started', 'in_progress', 'on_hold'])
            ->count();

        $this->projects = Project::query()
            ->with([
                'tasks' => fn ($q) => $q->whereNotIn('status', ['done', 'cancelled'])->orderBy('start_date'),
                'tasks.dependencies',
            ])
            ->whereIn('status', ['not_started', 'in_progress', 'on_hold'])
            ->orderByDesc('updated_at')
            ->limit(10)
            ->get()
            ->map(fn ($p) => [
                'id' => $p->id,
                'name' => $p->name,
                'status' => $p->status,
                'tasks' => $p->tasks->map(fn ($t) => [
                    'id' => $t->id,
                    'title' => $t->title,
                    'start' => $t->start_date?->format('Y-m-d') ?? $t->created_at->format('Y-m-d'),
                    'end' => $t->due_date?->format('Y-m-d') ?? now()->addDays(7)->format('Y-m-d'),
                    'progress' => $t->status === 'done' ? 100 : ($t->status === 'in_progress' ? 50 : ($t->status === 'in_review' ? 80 : 0)),
                    'status' => $t->status,
                    'dependencies' => $t->dependencies->pluck('id')->join(','),
                ])->toArray(),
            ])
            ->toArray();
    }

    /**
     * Dipanggil dari drag-and-drop Gantt (frappe-gantt on_date_change).
     */
    public function updateTaskDate(int $taskId, string $start, string $end): void
    {
        $task = Task::find($taskId);

        if (! $task) {
            return;
        }

        $task->update([
            'start_date' => $start,
            'due_date' => $end,
        ]);

        Notification::make()
            ->title('Jadwal task diperbarui')
            ->body($task->title . ' — ' . $start . ' → ' . $end)
            ->success()
            ->send();
    }
}
