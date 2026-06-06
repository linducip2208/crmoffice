<?php

namespace App\Models;

use App\Models\Concerns\HasReminders;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Laravel\Scout\Searchable;

class Lead extends Model
{
    use HasFactory, HasReminders, Searchable, SoftDeletes;

    public function toSearchableArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'company' => $this->company,
            'email' => $this->email,
            'phone' => $this->phone,
        ];
    }


    protected $fillable = [
        'name', 'company', 'email', 'phone', 'website',
        'address', 'city', 'country',
        'estimated_value', 'currency_id', 'lead_source_id', 'lead_status_id',
        'assigned_to', 'description', 'expected_close', 'converted_at',
        'converted_to_client_id', 'custom_fields', 'last_activity_at',
        'lead_score', 'lead_score_level', 'lead_score_factors',
    ];

    protected $casts = [
        'estimated_value' => 'decimal:2',
        'expected_close' => 'date',
        'converted_at' => 'datetime',
        'last_activity_at' => 'datetime',
        'custom_fields' => 'array',
        'lead_score' => 'integer',
        'lead_score_factors' => 'array',
    ];

    public function currency(): BelongsTo
    {
        return $this->belongsTo(Currency::class);
    }

    public function source(): BelongsTo
    {
        return $this->belongsTo(LeadSource::class, 'lead_source_id');
    }

    public function status(): BelongsTo
    {
        return $this->belongsTo(LeadStatus::class, 'lead_status_id');
    }

    public function assignedTo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function convertedClient(): BelongsTo
    {
        return $this->belongsTo(Client::class, 'converted_to_client_id');
    }

    public function proposals()
    {
        return $this->hasMany(Proposal::class);
    }

    public function activities(): MorphMany
    {
        return $this->morphMany(Activity::class, 'subject');
    }

    public function notes(): MorphMany
    {
        return $this->morphMany(Note::class, 'notable');
    }
}
