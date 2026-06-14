<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Setting extends Model
{
    protected $fillable = [
        'company_id',
        'key',
        'value',
        'type',
    ];

    protected $casts = [
        'value' => 'string',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(\App\Modules\Sirket\Models\Company::class);
    }

    public function scopeForCompany($query, ?int $companyId)
    {
        return $query->where('company_id', $companyId);
    }

    public function scopeByKey($query, string $key)
    {
        return $query->where('key', $key);
    }

    public function getTypedValueAttribute(): mixed
    {
        return match ($this->type) {
            'boolean' => filter_var($this->value, FILTER_VALIDATE_BOOLEAN),
            'integer' => (int) $this->value,
            'json'    => json_decode($this->value, true),
            default   => $this->value,
        };
    }

    public static function get(int $companyId, string $key, mixed $default = null): mixed
    {
        $setting = static::forCompany($companyId)->byKey($key)->first();
        if (!$setting) return $default;
        return $setting->typed_value;
    }

    public static function set(int $companyId, string $key, mixed $value, string $type = 'string'): void
    {
        $val = is_array($value) ? json_encode($value, JSON_UNESCAPED_UNICODE) : $value;
        $type = is_array($value) ? 'json' : $type;

        static::updateOrCreate(
            ['company_id' => $companyId, 'key' => $key],
            ['value' => (string) $val, 'type' => $type]
        );
    }

    public static function setMany(int $companyId, array $data): void
    {
        foreach ($data as $key => $value) {
            $type = 'string';
            if (is_bool($value)) $type = 'boolean';
            elseif (is_int($value)) $type = 'integer';
            elseif (is_array($value)) $type = 'json';
            static::set($companyId, $key, $value, $type);
        }
    }
}
