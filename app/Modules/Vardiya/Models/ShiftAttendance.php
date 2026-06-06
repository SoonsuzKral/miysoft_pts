<?php

namespace App\Modules\Vardiya\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class ShiftAttendance extends Model
{
    protected $table = 'shift_attendances';

    protected $fillable = [
        'company_id', 'shift_assignment_id', 'personel_id', 'shift_id', 'date',
        'clock_in', 'clock_out', 'status', 'late_minutes', 'early_leave_minutes',
        'note', 'clock_in_source', 'clock_out_source', 'clocked_in_by', 'clocked_out_by',
    ];

    protected $casts = [
        'date'       => 'date',
        'clock_in'   => 'datetime',
        'clock_out'  => 'datetime',
        'late_minutes'       => 'integer',
        'early_leave_minutes'=> 'integer',
    ];

    public const STATUS_PENDING   = 'pending';
    public const STATUS_ON_SHIFT  = 'on_shift';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_MISSED    = 'missed';
    public const STATUS_LATE      = 'late';
    public const STATUS_LEFT_EARLY = 'left_early';

    public function company(): BelongsTo
    {
        return $this->belongsTo(\App\Modules\Sirket\Models\Company::class);
    }

    public function shiftAssignment(): BelongsTo
    {
        return $this->belongsTo(ShiftAssignment::class);
    }

    public function personel(): BelongsTo
    {
        return $this->belongsTo(\App\Modules\Personel\Models\Personel::class);
    }

    public function shift(): BelongsTo
    {
        return $this->belongsTo(Shift::class);
    }

    public function clockedInBy(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'clocked_in_by');
    }

    public function clockedOutBy(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'clocked_out_by');
    }

    public function getDurationMinutesAttribute(): int
    {
        if (!$this->clock_in || !$this->clock_out) return 0;

        $start = Carbon::parse($this->clock_in);
        $end   = Carbon::parse($this->clock_out);

        return $start->diffInMinutes($end);
    }

    public function scopeForCompany($query, ?int $companyId)
    {
        return $query->where('company_id', $companyId);
    }

    public function scopeForDate($query, $date)
    {
        return $query->where('date', $date);
    }

    public function scopeForPersonel($query, int $personelId)
    {
        return $query->where('personel_id', $personelId);
    }

    public function scopeToday($query)
    {
        return $query->where('date', today());
    }

    public function scopeActive($query)
    {
        return $query->whereIn('status', [self::STATUS_ON_SHIFT, self::STATUS_LATE]);
    }
}
