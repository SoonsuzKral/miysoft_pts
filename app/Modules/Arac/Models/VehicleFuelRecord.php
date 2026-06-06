<?php

namespace App\Modules\Arac\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VehicleFuelRecord extends Model
{
    protected $table = 'vehicle_fuel_records';

    protected $fillable = [
        'company_id', 'vehicle_id', 'date', 'km', 'liters', 'unit_price',
        'total_cost', 'fuel_type', 'station', 'full_refill', 'notes', 'created_by',
    ];

    protected $casts = [
        'date'        => 'date',
        'km'          => 'decimal:2',
        'liters'      => 'decimal:2',
        'unit_price'  => 'decimal:3',
        'total_cost'  => 'decimal:2',
        'full_refill' => 'boolean',
    ];

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'created_by');
    }

    public function scopeForCompany($query, ?int $companyId)
    {
        return $query->where('company_id', $companyId);
    }
}
