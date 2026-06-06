<?php

namespace App\Modules\CMS\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Content extends Model
{
    protected $table = 'contents';

    protected $fillable = [
        'key', 'section', 'label', 'value', 'type', 'meta', 'is_active', 'updated_by',
    ];

    protected $casts = [
        'meta'      => 'array',
        'is_active' => 'boolean',
    ];

    /** Key'e göre içerik değeri getir (cache destekli) */
    public static function get(string $key, string $default = ''): string
    {
        return Cache::remember("content:{$key}", 3600, function () use ($key, $default) {
            return static::where('key', $key)->value('value') ?? $default;
        });
    }

    /** Bir section'daki tüm içerikleri key=>value çifti olarak getir */
    public static function section(string $section): array
    {
        return Cache::remember("content_section:{$section}", 3600, function () use ($section) {
            return static::where('section', $section)
                ->pluck('value', 'key')
                ->toArray();
        });
    }

    /** Kaydedince cache temizle */
    protected static function booted(): void
    {
        static::saved(fn ($m) => Cache::forget("content:{$m->key}"));
        static::saved(fn ($m) => $m->section && Cache::forget("content_section:{$m->section}"));
    }

    public function scopeBySection($query, string $section)
    {
        return $query->where('section', $section);
    }
}
