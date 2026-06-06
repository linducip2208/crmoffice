<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class Contact extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes;

    protected $guard = 'portal';

    protected $fillable = [
        'client_id', 'first_name', 'last_name', 'email', 'phone', 'position',
        'is_primary', 'portal_access', 'password',
        'receives_invoice_emails', 'receives_ticket_emails', 'receives_project_emails',
        'locale',
    ];

    protected $hidden = ['password', 'remember_token', 'invitation_token'];

    protected $casts = [
        'is_primary' => 'boolean',
        'portal_access' => 'boolean',
        'receives_invoice_emails' => 'boolean',
        'receives_ticket_emails' => 'boolean',
        'receives_project_emails' => 'boolean',
        'password' => 'hashed',
        'last_login_at' => 'datetime',
        'invitation_expires_at' => 'datetime',
    ];

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function getFullNameAttribute(): string
    {
        return trim("{$this->first_name} {$this->last_name}");
    }
}
