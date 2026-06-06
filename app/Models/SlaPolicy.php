<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SlaPolicy extends Model
{
    protected $fillable = ['name', 'rules', 'is_active'];

    protected $casts = [
        'rules' => 'array',
        'is_active' => 'boolean',
    ];
}
