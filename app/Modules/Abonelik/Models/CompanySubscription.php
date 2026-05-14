<?php
namespace App\Modules\Abonelik\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CompanySubscription extends Model
{
    protected $table = 'company_subscriptions';
    protected $fillable = [
        'company_id', 'plan_id', 'status', 'billing_cycle', 'price',
        'started_at', 'ends_at', 'trial_ends_at', 'cancelled_at', 'metadata',
    ];
    protected $casts = [
        'metadata'      => 'array',
        'price'         => 'decimal:2',
        'started_at'    => 'datetime',
        'ends_at'       => 'datetime',
        'trial_ends_at' => 'datetime',
        'cancelled_at'  => 'datetime',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(\App\Modules\Sirket\Models\Company::class);
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(SubscriptionPlan::class, 'plan_id');
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class, 'subscription_id');
    }

    public function isActive(): bool
    {
        return in_array($this->status, ['active', 'trial'])
            && ($this->ends_at === null || $this->ends_at->isFuture());
    }

    public function isOnTrial(): bool
    {
        return $this->status === 'trial'
            && $this->trial_ends_at
            && $this->trial_ends_at->isFuture();
    }

    public function getDaysRemainingAttribute(): int
    {
        return max(0, (int) now()->diffInDays($this->ends_at, false));
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'trial'     => 'Deneme',
            'active'    => 'Aktif',
            'paused'    => 'Durduruldu',
            'cancelled' => 'İptal',
            'expired'   => 'Süresi Doldu',
            default     => $this->status,
        };
    }

    public function scopeForCompany($query, int $companyId)
    {
        return $query->where('company_id', $companyId);
    }
}
