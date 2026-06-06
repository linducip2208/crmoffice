<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TicketPriority extends Model
{
    use HasFactory;
    public $timestamps = false;

    protected $fillable = ['name', 'response_minutes_sla', 'resolve_minutes_sla', 'color', 'order', 'is_active'];

    protected $casts = ['is_active' => 'boolean'];
}
