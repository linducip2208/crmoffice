<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class CreditNote extends Model
{
    use HasFactory;
    protected $fillable = [
        'number', 'client_id', 'issue_date', 'total', 'applied_total',
        'refunded_total', 'currency_id', 'status', 'reason', 'created_by',
    ];

    protected $casts = [
        'issue_date' => 'date',
        'total' => 'decimal:2',
        'applied_total' => 'decimal:2',
        'refunded_total' => 'decimal:2',
    ];

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function currency(): BelongsTo
    {
        return $this->belongsTo(Currency::class);
    }

    public function invoices(): BelongsToMany
    {
        return $this->belongsToMany(Invoice::class, 'credit_note_invoices')
            ->withPivot('amount_applied', 'applied_at');
    }
}
