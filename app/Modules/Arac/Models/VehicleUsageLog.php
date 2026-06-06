<?php

namespace App\Modules\Arac\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VehicleUsageLog extends Model
{
    use SoftDeletes;

    protected $table = 'vehicle_usage_logs';

    protected $fillable = [
        'company_id', 'vehicle_id', 'personel_id',
        'start_km', 'end_km', 'start_date', 'end_date',
        'start_time', 'end_time', 'origin', 'destination',
        'purpose', 'notes', 'status', 'created_by',
    ];

    protected $casts = [
        'start_km'   => 'decimal:2',
        'end_km'     => 'decimal:2',
        'start_date' => 'date',
        'end_date'   => 'date',
    ];

    const STATUS_ACTIVE   = 'active';
    const STATUS_COMPLETED = 'completed';
    const STATUS_CANCELLED = 'cancelled';

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function personel(): BelongsTo
    {
        return $this->belongsTo(\App\Modules\Personel\Models\Personel::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'created_by');
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'active'    => 'Aktif',
            'completed' => 'Tamamlandı',
            'cancelled' => 'İptal',
            default     => $this->status,
        };
    }

    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            'active'    => 'green',
            'completed' => 'blue',
            'cancelled' => 'gray',
            default     => 'gray',
        };
    }

    public function scopeForCompany($query, ?int $companyId)
    {
        return $query->where('company_id', $companyId);
    }
}
