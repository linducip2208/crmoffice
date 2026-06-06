<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TicketAttachment extends Model
{
    public $timestamps = false;
    protected $fillable = ['ticket_id', 'ticket_reply_id', 'file_id', 'created_at'];
    protected $casts = ['created_at' => 'datetime'];

    public function ticket(): BelongsTo
    {
        return $this->belongsTo(Ticket::class);
    }

    public function file(): BelongsTo
    {
        return $this->belongsTo(File::class);
    }
}
