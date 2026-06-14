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

    public function canStartTrial(): bool
    {
        if ($this->status === 'trial') {
            return false;
        }
        $trialUsed = self::where('company_id', $this->company_id)
            ->whereNotNull('trial_ends_at')
            ->exists();
        return !$trialUsed;
    }

    public function getDaysRemainingAttribute(): int
    {
        if ($this->isOnTrial()) {
            return max(0, (int) now()->diffInDays($this->trial_ends_at, false));
        }
        return max(0, (int) now()->diffInDays($this->ends_at, false));
    }

    public function getTrialDaysUsedAttribute(): int
    {
        if (!$this->trial_ends_at) return 0;
        return (int) $this->trial_ends_at->diffInDays($this->started_at);
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'trial'     => 'Deneme',
            'active'    => 'Aktif',
            'paused'    => 'Durduruldu',
            'cancelled' => 'İptal Edildi',
            'expired'   => 'Süresi Doldu',
            default     => $this->status,
        };
    }

    public function scopeForCompany($query, ?int $companyId)
    {
        return $query->where('company_id', $companyId);
    }

    public function scopeActive($query)
    {
        return $query->whereIn('status', ['active', 'trial'])->where(fn ($q) => $q->whereNull('ends_at')->orWhere('ends_at', '>', now()));
    }

    public function scopeExpired($query)
    {
        return $query->where('status', 'expired')->orWhere(fn ($q) => $q->whereNotNull('ends_at')->where('ends_at', '<=', now()));
    }
}
