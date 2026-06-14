<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Modules\Lokasyon\Models\LocationType;

class LocationTypeSeeder extends Seeder
{
    public function run(): void
    {
        $types = [
            ['name' => 'Ofis',     'slug' => 'ofis',     'icon' => '🏢', 'color' => '#3B82F6', 'is_active' => true],
            ['name' => 'Depo',     'slug' => 'depo',     'icon' => '🏭', 'color' => '#F59E0B', 'is_active' => true],
            ['name' => 'Kurum',    'slug' => 'kurum',    'icon' => '🏛️', 'color' => '#8B5CF6', 'is_active' => true],
            ['name' => 'Ev',       'slug' => 'ev',       'icon' => '🏠', 'color' => '#10B981', 'is_active' => true],
            ['name' => 'Diğer',    'slug' => 'diger',    'icon' => '📍', 'color' => '#6B7280', 'is_active' => true],
        ];

        foreach ($types as $type) {
            LocationType::firstOrCreate(['slug' => $type['slug']], $type);
        }
    }
}
