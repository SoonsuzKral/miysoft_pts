<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('vehicles', function (Blueprint $table) {
            $table->decimal('current_km', 12, 2)->nullable()->after('next_maintenance_date');
            $table->decimal('last_maintenance_km', 12, 2)->nullable()->after('current_km');
            $table->decimal('engine_capacity', 4, 1)->nullable()->after('fuel_type');
            $table->decimal('fuel_consumption_avg', 5, 2)->nullable()->after('engine_capacity');
            $table->decimal('fuel_tank_capacity', 6, 1)->nullable()->after('fuel_consumption_avg');
            $table->date('insurance_date')->nullable()->after('fuel_tank_capacity');
            $table->date('traffic_date')->nullable()->after('insurance_date');
            $table->date('examination_date')->nullable()->after('traffic_date');
        });
    }

    public function down(): void
    {
        Schema::table('vehicles', function (Blueprint $table) {
            $table->dropColumn([
                'current_km', 'last_maintenance_km', 'engine_capacity',
                'fuel_consumption_avg', 'fuel_tank_capacity',
                'insurance_date', 'traffic_date', 'examination_date',
            ]);
        });
    }
};
