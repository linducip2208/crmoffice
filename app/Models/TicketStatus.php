<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TicketStatus extends Model
{
    use HasFactory;
    public $timestamps = false;

    protected $fillable = ['name', 'is_open', 'is_resolved', 'order', 'color'];

    protected $casts = [
        'is_open' => 'boolean',
        'is_resolved' => 'boolean',
    ];
}
