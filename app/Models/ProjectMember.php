<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProjectMember extends Model
{
    public $timestamps = false;

    protected $fillable = ['project_id', 'user_id', 'role', 'added_at'];

    protected $casts = ['added_at' => 'datetime'];
}
