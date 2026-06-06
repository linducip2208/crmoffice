<?php

namespace App\Models;

use App\Models\Concerns\HasReminders;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Laravel\Scout\Searchable;

class Task extends Model
{
    use HasFactory, HasReminders, Searchable, SoftDeletes;

    public function toSearchableArray(): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'status' => $this->status,
        ];
    }


    protected $fillable = [
        'project_id', 'milestone_id', 'parent_task_id', 'title', 'description',
        'priority', 'status', 'start_date', 'due_date', 'estimate_hours',
        'is_billable', 'hourly_rate', 'is_visible_to_customer', 'order',
        'completed_at', 'created_by', 'custom_fields',
    ];

    protected $casts = [
        'start_date' => 'date',
        'due_date' => 'date',
        'estimate_hours' => 'decimal:2',
        'hourly_rate' => 'decimal:2',
        'is_billable' => 'boolean',
        'is_visible_to_customer' => 'boolean',
        'completed_at' => 'datetime',
        'custom_fields' => 'array',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function milestone(): BelongsTo
    {
        return $this->belongsTo(Milestone::class);
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Task::class, 'parent_task_id');
    }

    public function subtasks(): HasMany
    {
        return $this->hasMany(Task::class, 'parent_task_id');
    }

    public function assignees(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'task_assignees')
            ->withPivot('assigned_at');
    }

    public function checklist(): HasMany
    {
        return $this->hasMany(TaskChecklist::class)->orderBy('order');
    }

    public function dependencies(): BelongsToMany
    {
        return $this->belongsToMany(Task::class, 'task_dependencies', 'task_id', 'depends_on_task_id')
            ->withPivot('type');
    }

    public function timeEntries(): HasMany
    {
        return $this->hasMany(TimeEntry::class);
    }

    public function notes(): MorphMany
    {
        return $this->morphMany(Note::class, 'notable');
    }

    public function getSubtaskCountAttribute(): int
    {
        return $this->subtasks()->count();
    }

    public function getIsSubtaskAttribute(): bool
    {
        return $this->parent_task_id !== null;
    }
}
