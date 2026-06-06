<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TaxRate extends Model
{
    use HasFactory;
    protected $fillable = ['name', 'percentage', 'is_compound', 'is_active'];

    protected $casts = [
        'percentage' => 'decimal:4',
        'is_compound' => 'boolean',
        'is_active' => 'boolean',
    ];
}
