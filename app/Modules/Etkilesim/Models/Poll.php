<?php
namespace App\Modules\Etkilesim\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Poll extends Model
{
    protected $table = 'polls';

    protected $fillable = [
        'company_id', 'question', 'options', 'multiple_choice',
        'anonymous', 'ends_at', 'is_active', 'created_by',
    ];

    protected $casts = [
        'options'         => 'array',
        'multiple_choice' => 'boolean',
        'anonymous'       => 'boolean',
        'is_active'       => 'boolean',
        'ends_at'         => 'datetime',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(\App\Modules\Sirket\Models\Company::class);
    }

    public function responses(): HasMany
    {
        return $this->hasMany(PollResponse::class);
    }

    public function isExpired(): bool
    {
        return $this->ends_at && $this->ends_at->isPast();
    }

    /** Seçenek bazında oy sayılarını döndür */
    public function getResultsAttribute(): array
    {
        $responses = $this->responses()->get();
        $counts = array_fill(0, count($this->options ?? []), 0);
        foreach ($responses as $r) {
            foreach ($r->selected_options as $idx) {
                if (isset($counts[$idx])) $counts[$idx]++;
            }
        }
        return $counts;
    }

    public function scopeForCompany($query, ?int $companyId)
    {
        return $query->where('company_id', $companyId);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true)
            ->where(fn ($q) => $q->whereNull('ends_at')->orWhere('ends_at', '>', now()));
    }
}
