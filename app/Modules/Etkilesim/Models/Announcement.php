<?php

namespace App\Modules\Etkilesim\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Announcement extends Model
{
    use SoftDeletes;

    protected $table = 'announcements';

    protected $fillable = [
        'company_id', 'title', 'content', 'type', 'visibility',
        'target_audience', 'is_pinned', 'is_published', 'publish_at',
        'expires_at', 'attachment', 'created_by',
    ];

    protected $casts = [
        'target_audience' => 'array',
        'is_pinned'       => 'boolean',
        'is_published'    => 'boolean',
        'publish_at'      => 'datetime',
        'expires_at'      => 'datetime',
    ];

    const TYPE_GENERAL = 'general';
    const TYPE_URGENT  = 'urgent';
    const TYPE_EVENT   = 'event';

    public function company(): BelongsTo
    {
        return $this->belongsTo(\App\Modules\Sirket\Models\Company::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'created_by');
    }

    public function isExpired(): bool
    {
        return $this->expires_at && $this->expires_at->isPast();
    }

    public function getTypeLabelAttribute(): string
    {
        return match ($this->type) {
            'urgent'  => 'Acil',
            'event'   => 'Etkinlik',
            default   => 'Genel',
        };
    }

    public function getTypeColorAttribute(): string
    {
        return match ($this->type) {
            'urgent'  => 'red',
            'event'   => 'blue',
            default   => 'gray',
        };
    }

    public function scopeForCompany($query, ?int $companyId)
    {
        return $query->where('company_id', $companyId);
    }

    public function scopeActive($query)
    {
        return $query->where('is_published', true)
            ->where(fn ($q) => $q->whereNull('publish_at')->orWhere('publish_at', '<=', now()))
            ->where(fn ($q) => $q->whereNull('expires_at')->orWhere('expires_at', '>', now()));
    }

    public function scopePinned($query)
    {
        return $query->where('is_pinned', true);
    }
}
