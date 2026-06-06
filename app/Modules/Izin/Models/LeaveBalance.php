<?php

namespace App\Modules\Izin\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LeaveBalance extends Model
{
    protected $table = 'leave_balances';

    protected $fillable = [
        'personel_id',
        'leave_type_id',
        'year',
        'entitled_days',
        'used_days',
        'remaining_days',
    ];

    protected $casts = [
        'entitled_days'  => 'decimal:2',
        'used_days'      => 'decimal:2',
        'remaining_days' => 'decimal:2',
        'year'           => 'integer',
    ];

    public function personel(): BelongsTo
    {
        return $this->belongsTo(\App\Modules\Personel\Models\Personel::class);
    }

    public function leaveType(): BelongsTo
    {
        return $this->belongsTo(LeaveType::class);
    }

    // ─── İş Mantığı ──────────────────────────────────────────────────────────

    /** Bakiyeden gün düş (izin onaylandığında çağrılır) */
    public function deduct(float $days): bool
    {
        if ($this->remaining_days < $days) {
            return false;
        }
        $this->increment('used_days', $days);
        $this->decrement('remaining_days', $days);
        return true;
    }

    /** Bakiyeye gün ekle (izin iptal/ret edildiğinde çağrılır) */
    public function restore(float $days): void
    {
        $this->decrement('used_days', min($days, $this->used_days));
        $this->increment('remaining_days', $days);
    }

    /** Yeterli bakiye var mı? */
    public function hasSufficient(float $days): bool
    {
        // Ücretsiz izin türleri için bakiye kontrolü yapma
        if (!$this->leaveType?->paid) return true;
        return $this->remaining_days >= $days;
    }

    /** Recalculate remaining based on entitled and used */
    public function recalculate(): void
    {
        $this->remaining_days = max(0, $this->entitled_days - $this->used_days);
        $this->save();
    }

    // ─── Scopes ──────────────────────────────────────────────────────────────

    public function scopeForPersonel($query, int $personelId)
    {
        return $query->where('personel_id', $personelId);
    }

    public function scopeForYear($query, int $year)
    {
        return $query->where('year', $year);
    }
}
