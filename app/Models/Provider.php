<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Crypt;

class Provider extends Model
{
    protected $fillable = [
        'name', 'type', 'api_format', 'base_url', 'api_key_encrypted',
        'extra_headers', 'extra_config', 'is_active', 'priority', 'created_by',
    ];

    protected $casts = [
        'extra_headers' => 'array',
        'extra_config' => 'array',
        'is_active' => 'boolean',
    ];

    protected $hidden = ['api_key_encrypted'];

    protected function apiKey(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->api_key_encrypted ? Crypt::decryptString($this->api_key_encrypted) : null,
            set: fn ($value) => ['api_key_encrypted' => $value ? Crypt::encryptString($value) : null],
        );
    }

    public function credentials(): HasMany
    {
        return $this->hasMany(ProviderCredential::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }
}
