<?php

namespace App\Modules\Arac\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Vehicle extends Model
{
    use SoftDeletes;

    protected $table = 'vehicles';

    protected $fillable = [
        'company_id', 'plate', 'brand', 'model', 'year', 'color', 'vin',
        'engine_type', 'fuel_type', 'acquisition_date', 'acquisition_cost',
        'status', 'assigned_personel_id', 'last_maintenance_date',
        'next_maintenance_date', 'current_km', 'last_maintenance_km',
        'engine_capacity', 'fuel_consumption_avg', 'fuel_tank_capacity',
        'insurance_date', 'traffic_date', 'examination_date',
        'notes', 'created_by',
    ];

    protected $casts = [
        'year'                  => 'integer',
        'acquisition_date'      => 'date',
        'acquisition_cost'      => 'decimal:2',
        'last_maintenance_date' => 'date',
        'next_maintenance_date' => 'date',
        'current_km'            => 'decimal:2',
        'last_maintenance_km'   => 'decimal:2',
        'engine_capacity'       => 'decimal:1',
        'fuel_consumption_avg'  => 'decimal:2',
        'fuel_tank_capacity'    => 'decimal:1',
        'insurance_date'        => 'date',
        'traffic_date'          => 'date',
        'examination_date'      => 'date',
    ];

    const STATUS_ACTIVE         = 'active';
    const STATUS_MAINTENANCE    = 'maintenance';
    const STATUS_OUT_OF_SERVICE = 'out_of_service';

    public function company(): BelongsTo
    {
        return $this->belongsTo(\App\Modules\Sirket\Models\Company::class);
    }

    public function assignedPersonel(): BelongsTo
    {
        return $this->belongsTo(\App\Modules\Personel\Models\Personel::class, 'assigned_personel_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'created_by');
    }

    public function fuelRecords(): HasMany
    {
        return $this->hasMany(VehicleFuelRecord::class);
    }

    public function usageLogs(): HasMany
    {
        return $this->hasMany(VehicleUsageLog::class);
    }

    public function assignTo(?int $personelId): void
    {
        $this->update(['assigned_personel_id' => $personelId]);
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'active'         => 'Aktif',
            'maintenance'    => 'Bakımda',
            'out_of_service' => 'Hizmet Dışı',
            default          => $this->status,
        };
    }

    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            'active'         => 'green',
            'maintenance'    => 'yellow',
            'out_of_service' => 'red',
            default          => 'gray',
        };
    }

    public function getInsuranceExpiringAttribute(): bool
    {
        return $this->insurance_date && $this->insurance_date->isPast();
    }

    public function getTrafficExpiringAttribute(): bool
    {
        return $this->traffic_date && $this->traffic_date->isPast();
    }

    public function scopeForCompany($query, ?int $companyId)
    {
        return $query->where('company_id', $companyId);
    }

    public function scopeActive($query)
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }

    public function scopeMaintenance($query)
    {
        return $query->where('status', self::STATUS_MAINTENANCE);
    }
}
