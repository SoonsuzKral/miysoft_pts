<?php

namespace App\Modules\Lokasyon\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use App\Modules\Sirket\Models\Company;
use App\Modules\Personel\Models\Personel;

class Location extends Model
{
    use SoftDeletes;

    protected $table = 'locations';

    protected $fillable = [
        'company_id', 'name', 'location_type_id', 'address', 'city', 'district',
        'latitude', 'longitude', 'radius', 'color', 'description', 'is_active', 'created_by',
    ];

    protected $casts = [
        'latitude' => 'float',
        'longitude' => 'float',
        'radius' => 'integer',
        'is_active' => 'boolean',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function type(): BelongsTo
    {
        return $this->belongsTo(LocationType::class, 'location_type_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'created_by');
    }

    public function personels(): BelongsToMany
    {
        return $this->belongsToMany(Personel::class, 'location_personel')
            ->withPivot('type', 'is_primary', 'assigned_at')
            ->withTimestamps();
    }

    public function personelsByType(string $type): BelongsToMany
    {
        return $this->personels()->wherePivot('type', $type);
    }

    public function scopeForCompany($query, ?int $companyId)
    {
        return $companyId ? $query->where('company_id', $companyId) : $query;
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function getPersonelCountAttribute(): int
    {
        return $this->personels()->count();
    }

    public static function haversineDistance(float $lat1, float $lon1, float $lat2, float $lon2): float
    {
        $earthRadius = 6371000;
        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);
        $a = sin($dLat / 2) * sin($dLat / 2) +
             cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
             sin($dLon / 2) * sin($dLon / 2);
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
        return $earthRadius * $c;
    }

    public function isWithinRadius(float $userLat, float $userLon): bool
    {
        $distance = self::haversineDistance($userLat, $userLon, $this->latitude, $this->longitude);
        return $distance <= $this->radius;
    }
}
