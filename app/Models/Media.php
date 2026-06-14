<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class Media extends Model
{
    protected $table = 'media';
    const UPDATED_AT = null;

    protected $fillable = [
        'company_id',
        'model_type',
        'model_id',
        'disk',
        'path',
        'filename',
        'mime',
        'size',
        'metadata',
        'created_by',
    ];

    protected $casts = [
        'size'     => 'integer',
        'metadata' => 'array',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(\App\Modules\Sirket\Models\Company::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function scopeForCompany($query, ?int $companyId)
    {
        if ($companyId) {
            return $query->where(fn ($q) => $q->where('company_id', $companyId)->orWhereNull('company_id'));
        }
        return $query;
    }

    public function getUrlAttribute(): string
    {
        return Storage::disk($this->disk)->url($this->path);
    }

    public function getSizeFormattedAttribute(): string
    {
        if (!$this->size) return '—';
        if ($this->size < 1024) return $this->size . ' B';
        if ($this->size < 1048576) return round($this->size / 1024, 1) . ' KB';
        return round($this->size / 1048576, 1) . ' MB';
    }

    public function isImage(): bool
    {
        return $this->mime && str_starts_with($this->mime, 'image');
    }
}
