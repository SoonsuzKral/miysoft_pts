<?php

namespace App\Modules\Vardiya\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class ShiftAssignment extends Model
{
    protected $table = 'shift_assignments';

    protected $fillable = [
        'shift_plan_id', 'personel_id', 'shift_id', 'date', 'created_by',
    ];

    protected $casts = [
        'date' => 'date',
    ];

    // ¦¦¦ Relations ¦¦¦¦¦¦¦¦¦¦¦¦¦¦¦¦¦¦¦¦¦¦¦¦¦¦¦¦¦¦¦¦¦¦¦¦¦¦¦¦¦¦¦¦¦¦¦¦¦¦¦¦¦¦¦¦¦¦¦

    public function shiftPlan(): BelongsTo
    {
        return $this->belongsTo(ShiftPlan::class);
    }

    public function shift(): BelongsTo
    {
        return $this->belongsTo(Shift::class);
    }

    public function personel(): BelongsTo
    {
        return $this->belongsTo(\App\Modules\Personel\Models\Personel::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'created_by');
    }

    // ¦¦¦ Ýþ Mantýðý — Çakýþma Kontrolü ¦¦¦¦¦¦¦¦¦¦¦¦¦¦¦¦¦¦¦¦¦¦¦¦¦¦¦¦¦¦¦¦¦¦¦¦¦¦

    /**
     * Ayný personel, ayný gün baþka bir atama var mý?
     */
    public function hasPersonelConflict(): bool
    {
        return static::where('personel_id', $this->personel_id)
            ->where('date', $this->date)
            ->where('id', '!=', $this->id ?? 0)
            ->exists();
    }

    /**
     * Ayný personelin önceki gün gece vardiyasýyla çakýþýyor mu?
     * (Gece vardiyasý › ertesi sabah erken vardiya problemi)
     */
    public function hasPrevDayNightConflict(): bool
    {
        $prevAssignment = static::where('personel_id', $this->personel_id)
            ->where('date', Carbon::parse($this->date)->subDay()->toDateString())
            ->with('shift')
            ->first();

        return $prevAssignment?->shift?->is_night_shift === true;
    }

    // ¦¦¦ Scopes ¦¦¦¦¦¦¦¦¦¦¦¦¦¦¦¦¦¦¦¦¦¦¦¦¦¦¦¦¦¦¦¦¦¦¦¦¦¦¦¦¦¦¦¦¦¦¦¦¦¦¦¦¦¦¦¦¦¦¦¦¦¦

    public function scopeForPersonel($query, int $personelId)
    {
        return $query->where('personel_id', $personelId);
    }

    public function scopeForDateRange($query, string $from, string $to)
    {
        return $query->whereBetween('date', [$from, $to]);
    }

    public function scopeForCompany($query, ?int $companyId)
    {
        return $query->whereHas('shiftPlan', fn ($q) => $q->where('company_id', $companyId));
    }
}
