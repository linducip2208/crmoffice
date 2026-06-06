<?php

namespace App\Livewire;

use App\Models\Task;
use App\Models\TimeEntry;
use Illuminate\Support\Carbon;
use Livewire\Component;

class TaskTimer extends Component
{
    public Task $task;

    public ?TimeEntry $activeEntry = null;

    public string $note = '';

    public function mount(Task $task): void
    {
        $this->task = $task;
        $this->activeEntry = TimeEntry::query()
            ->where('user_id', auth()->id())
            ->whereNull('end_at')
            ->latest()
            ->first();
    }

    public function start(): void
    {
        if ($this->activeEntry) {
            $this->stop();
        }

        $this->activeEntry = TimeEntry::create([
            'task_id' => $this->task->id,
            'project_id' => $this->task->project_id,
            'user_id' => auth()->id(),
            'start_at' => now(),
            'is_billable' => $this->task->is_billable,
            'hourly_rate' => $this->task->hourly_rate ?? $this->task->project?->hourly_rate,
            'note' => $this->note ?: null,
        ]);
    }

    public function stop(): void
    {
        if (! $this->activeEntry) {
            return;
        }

        $this->activeEntry->update([
            'end_at' => now(),
            'minutes' => (int) Carbon::parse($this->activeEntry->start_at)->diffInMinutes(now()),
        ]);
        $this->activeEntry = null;
        $this->note = '';
    }

    public function render()
    {
        return view('livewire.task-timer');
    }
}
