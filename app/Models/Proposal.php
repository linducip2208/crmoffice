<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Proposal extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'number', 'subject', 'client_id', 'lead_id', 'content', 'total',
        'currency_id', 'open_until', 'status', 'is_template', 'public_token',
        'accepted_at', 'accepted_by_name', 'accepted_signature', 'accepted_ip',
        'declined_at', 'decline_reason', 'created_by',
    ];

    protected $casts = [
        'total' => 'decimal:2',
        'open_until' => 'date',
        'accepted_at' => 'datetime',
        'declined_at' => 'datetime',
        'is_template' => 'boolean',
    ];

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function lead(): BelongsTo
    {
        return $this->belongsTo(Lead::class);
    }

    public function currency(): BelongsTo
    {
        return $this->belongsTo(Currency::class);
    }
}
