<?php

namespace App\Models;

use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements FilamentUser
{
    use HasApiTokens, HasFactory, HasRoles, Notifiable, SoftDeletes;

    protected $fillable = [
        'name', 'email', 'password', 'phone', 'job_title', 'hourly_rate',
        'avatar_file_id', 'is_active', 'locale', 'timezone', 'notification_preferences',
        'ics_token',
    ];

    protected $hidden = [
        'password', 'remember_token', 'two_factor_secret', 'two_factor_recovery_codes',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'last_login_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
            'hourly_rate' => 'decimal:2',
            'notification_preferences' => 'array',
        ];
    }

    public function canAccessPanel(Panel $panel): bool
    {
        return $this->is_active && $this->hasAnyRole(['owner', 'admin', 'sales', 'pm', 'support', 'accountant', 'staff']);
    }

    public function avatar(): BelongsTo
    {
        return $this->belongsTo(File::class, 'avatar_file_id');
    }

    public function clientsManaged(): HasMany
    {
        return $this->hasMany(Client::class, 'account_manager_id');
    }

    public function assignedLeads(): HasMany
    {
        return $this->hasMany(Lead::class, 'assigned_to');
    }

    public function managedProjects(): HasMany
    {
        return $this->hasMany(Project::class, 'project_manager_id');
    }

    public function assignedTickets(): HasMany
    {
        return $this->hasMany(Ticket::class, 'assigned_to');
    }

    public function timeEntries(): HasMany
    {
        return $this->hasMany(TimeEntry::class);
    }

    /**
     * Check whether the user wants a given notification on a given channel.
     * Defaults to true if no explicit pref set (opt-out model).
     */
    public function wantsNotification(string $event, string $channel = 'database'): bool
    {
        $prefs = $this->notification_preferences ?? [];
        $eventPrefs = $prefs[$event] ?? null;

        if ($eventPrefs === null) {
            return true;
        }

        return (bool) ($eventPrefs[$channel] ?? true);
    }

    public function getOrCreateIcsToken(): string
    {
        if (! $this->ics_token) {
            $this->ics_token = \Illuminate\Support\Str::random(32);
            $this->save();
        }

        return $this->ics_token;
    }
}
