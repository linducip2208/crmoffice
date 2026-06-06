<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class CalendarEvent extends Model
{
    protected $fillable = [
        'user_id', 'title', 'description', 'starts_at', 'ends_at',
        'all_day', 'color', 'related_type', 'related_id', 'reminder_minutes_before',
        'reminder_sent_at',
    ];

    protected $casts = [
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
        'all_day' => 'boolean',
        'reminder_sent_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function related(): MorphTo
    {
        return $this->morphTo();
    }

    public function invitees(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'calendar_event_invitees', 'event_id', 'user_id')
            ->withPivot('response');
    }
}
