<div class="mt-6">
    <div class="rounded-lg border border-indigo-200 bg-indigo-50 dark:border-indigo-900 dark:bg-indigo-950/30 p-4">
        <div class="mb-2 flex items-center justify-between">
            <h3 class="text-sm font-semibold text-indigo-900 dark:text-indigo-200">⏱ Task Timer</h3>
            <a href="/admin/time-entries?tableFilters[task_id][value]={{ $task->id }}" class="text-xs text-indigo-600 dark:text-indigo-400 hover:underline">View time entries →</a>
        </div>
        @livewire('task-timer', ['task' => $task], key('task-timer-' . $task->id))
    </div>
</div>
