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

class Invoice extends Model
{
    use HasFactory, HasReminders, Searchable, SoftDeletes;

    public function toSearchableArray(): array
    {
        return [
            'id' => $this->id,
            'number' => $this->number,
            'status' => $this->status,
        ];
    }


    protected $fillable = [
        'number', 'client_id', 'project_id', 'estimate_id', 'recurring_parent_id',
        'invoice_date', 'due_date', 'currency_id',
        'subtotal', 'discount_total', 'tax_total', 'total', 'paid_total', 'balance_due',
        'status', 'is_recurring', 'recurring_period', 'recurring_count', 'recurring_remaining',
        'next_recurring_date', 'notes', 'terms', 'public_token',
        'pdf_file_id', 'sent_at', 'viewed_at', 'created_by',
        'last_reminded_at', 'late_fee_percent', 'late_fee_fixed', 'late_fee_charged_at',
    ];

    protected $casts = [
        'invoice_date' => 'date',
        'due_date' => 'date',
        'next_recurring_date' => 'date',
        'subtotal' => 'decimal:2',
        'discount_total' => 'decimal:2',
        'tax_total' => 'decimal:2',
        'total' => 'decimal:2',
        'paid_total' => 'decimal:2',
        'balance_due' => 'decimal:2',
        'late_fee_percent' => 'decimal:2',
        'late_fee_fixed' => 'decimal:2',
        'late_fee_charged_at' => 'datetime',
        'is_recurring' => 'boolean',
        'sent_at' => 'datetime',
        'viewed_at' => 'datetime',
        'last_reminded_at' => 'datetime',
    ];

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function estimate(): BelongsTo
    {
        return $this->belongsTo(Estimate::class);
    }

    public function currency(): BelongsTo
    {
        return $this->belongsTo(Currency::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(InvoiceItem::class)->orderBy('order');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function creditNotes(): BelongsToMany
    {
        return $this->belongsToMany(CreditNote::class, 'credit_note_invoices')
            ->withPivot('amount_applied', 'applied_at');
    }

    public function pdfFile(): BelongsTo
    {
        return $this->belongsTo(File::class, 'pdf_file_id');
    }

    public function activities(): MorphMany
    {
        return $this->morphMany(Activity::class, 'subject');
    }
}
