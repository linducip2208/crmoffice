<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class File extends Model
{
    protected $fillable = [
        'disk', 'path', 'original_name', 'mime', 'size_bytes',
        'uploaded_by', 'attachable_type', 'attachable_id', 'is_public',
    ];

    protected $casts = [
        'is_public' => 'boolean',
        'size_bytes' => 'integer',
    ];

    public function attachable(): MorphTo
    {
        return $this->morphTo();
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }
}
