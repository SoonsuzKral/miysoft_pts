<?php

namespace App\Modules\Lokasyon\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LocationType extends Model
{
    protected $table = 'location_types';

    protected $fillable = ['name', 'slug', 'icon', 'color', 'is_active'];

    protected $casts = ['is_active' => 'boolean'];

    public function locations(): HasMany
    {
        return $this->hasMany(Location::class, 'location_type_id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
