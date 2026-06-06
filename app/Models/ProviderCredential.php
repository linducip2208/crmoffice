<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Crypt;

class ProviderCredential extends Model
{
    public $timestamps = false;

    protected $fillable = ['provider_id', 'key', 'value_encrypted', 'is_secret'];

    protected $casts = ['is_secret' => 'boolean'];

    protected $hidden = ['value_encrypted'];

    protected function value(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->value_encrypted ? Crypt::decryptString($this->value_encrypted) : null,
            set: fn ($value) => ['value_encrypted' => $value ? Crypt::encryptString($value) : null],
        );
    }

    public function provider(): BelongsTo
    {
        return $this->belongsTo(Provider::class);
    }
}
