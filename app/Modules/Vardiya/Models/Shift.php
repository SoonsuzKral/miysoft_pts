<?php

namespace App\Modules\Vardiya\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Carbon\Carbon;

class Shift extends Model
{
    use SoftDeletes;

    protected $table = 'shifts';

    protected $fillable = [
        'company_id', 'name', 'start_time', 'end_time',
        'breaks', 'is_night_shift', 'color', 'is_active',
    ];

    protected $casts = [
        'breaks'        => 'array',
        'is_night_shift'=> 'boolean',
        'is_active'     => 'boolean',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(\App\Modules\Sirket\Models\Company::class);
    }

    public function assignments(): HasMany
    {
        return $this->hasMany(ShiftAssignment::class);
    }

    /** Vardiya süresini dakika olarak hesapla */
    public function getDurationMinutesAttribute(): int
    {
        $start = Carbon::parse($this->start_time);
        $end   = Carbon::parse($this->end_time);

        // Gece vardiyasý: bitiþ ertesi gün
        if ($this->is_night_shift && $end->lt($start)) {
            $end->addDay();
        }

        $total = $start->diffInMinutes($end);

        // Mola sürelerini düþ
        foreach ($this->breaks ?? [] as $break) {
            if (isset($break['duration_minutes'])) {
                $total -= (int) $break['duration_minutes'];
            }
        }

        return max(0, $total);
    }

    /** Vardiya süresini saat:dakika formatýnda göster */
    public function getDurationLabelAttribute(): string
    {
        $minutes = $this->duration_minutes;
        $h = intdiv($minutes, 60);
        $m = $minutes % 60;
        return $h . 's' . ($m > 0 ? ' ' . $m . 'dk' : '');
    }

    /** Bu vardiyayla çakýþýyor mu? */
    public function overlapsWithTime(string $checkStart, string $checkEnd): bool
    {
        $s  = Carbon::parse($this->start_time);
        $e  = Carbon::parse($this->end_time);
        $cs = Carbon::parse($checkStart);
        $ce = Carbon::parse($checkEnd);

        if ($this->is_night_shift && $e->lt($s)) $e->addDay();

        return $cs->lt($e) && $ce->gt($s);
    }

    public function scopeForCompany($query, ?int $companyId)
    {
        return $query->where('company_id', $companyId);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
