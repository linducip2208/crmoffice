<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Contract extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'number', 'subject', 'client_id', 'content',
        'start_date', 'end_date', 'contract_value', 'currency_id',
        'status', 'is_template', 'public_token',
        'signed_at', 'signed_by_name', 'signed_signature', 'signed_ip',
        'notify_expiry_days_before', 'created_by',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'contract_value' => 'decimal:2',
        'signed_at' => 'datetime',
        'is_template' => 'boolean',
    ];

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function currency(): BelongsTo
    {
        return $this->belongsTo(Currency::class);
    }
}
