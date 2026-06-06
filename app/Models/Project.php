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

class Project extends Model
{
    use HasFactory, HasReminders, Searchable, SoftDeletes;

    public function toSearchableArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'status' => $this->status,
        ];
    }


    protected $fillable = [
        'name', 'description', 'client_id', 'project_manager_id',
        'start_date', 'deadline', 'estimate_hours', 'billing_method',
        'fixed_price', 'hourly_rate', 'currency_id', 'status', 'progress_pct',
        'is_visible_to_customer', 'custom_fields',
    ];

    protected $casts = [
        'start_date' => 'date',
        'deadline' => 'date',
        'estimate_hours' => 'decimal:2',
        'fixed_price' => 'decimal:2',
        'hourly_rate' => 'decimal:2',
        'progress_pct' => 'decimal:2',
        'is_visible_to_customer' => 'boolean',
        'custom_fields' => 'array',
    ];

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function projectManager(): BelongsTo
    {
        return $this->belongsTo(User::class, 'project_manager_id');
    }

    public function currency(): BelongsTo
    {
        return $this->belongsTo(Currency::class);
    }

    public function members(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'project_members')
            ->withPivot('role', 'added_at');
    }

    public function milestones(): HasMany
    {
        return $this->hasMany(Milestone::class);
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class);
    }

    public function timeEntries(): HasMany
    {
        return $this->hasMany(TimeEntry::class);
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    public function expenses(): HasMany
    {
        return $this->hasMany(Expense::class);
    }

    public function discussions(): HasMany
    {
        return $this->hasMany(Discussion::class);
    }

    public function activities(): MorphMany
    {
        return $this->morphMany(Activity::class, 'subject');
    }

    public function files(): MorphMany
    {
        return $this->morphMany(File::class, 'attachable');
    }
}
