<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Crypt;

class Setting extends Model
{
    public $timestamps = false;

    protected $fillable = ['key', 'value', 'type', 'group', 'is_encrypted'];

    protected $casts = ['is_encrypted' => 'boolean'];

    protected function value(): Attribute
    {
        return Attribute::make(
            get: function ($value) {
                if ($this->is_encrypted && $value) {
                    $value = Crypt::decryptString($value);
                }

                return match ($this->type) {
                    'bool', 'boolean' => filter_var($value, FILTER_VALIDATE_BOOLEAN),
                    'int', 'integer' => (int) $value,
                    'float', 'decimal' => (float) $value,
                    'json', 'array' => json_decode((string) $value, true),
                    default => $value,
                };
            },
            set: function ($value) {
                if (in_array($this->type, ['json', 'array']) && ! is_string($value)) {
                    $value = json_encode($value);
                }
                if ($this->is_encrypted && $value !== null) {
                    $value = Crypt::encryptString((string) $value);
                }

                return $value;
            },
        );
    }

    public static function get(string $key, mixed $default = null): mixed
    {
        return Cache::rememberForever("setting:$key", fn () => self::query()->where('key', $key)->value('value') ?? $default);
    }

    public static function set(string $key, mixed $value): void
    {
        self::query()->updateOrCreate(['key' => $key], ['value' => $value]);
        Cache::forget("setting:$key");
    }
}
