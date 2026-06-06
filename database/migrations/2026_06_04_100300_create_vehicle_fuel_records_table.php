<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vehicle_fuel_records', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id')->index();
            $table->unsignedBigInteger('vehicle_id');
            $table->date('date');
            $table->decimal('km', 12, 2)->nullable();
            $table->decimal('liters', 10, 2);
            $table->decimal('unit_price', 8, 3);
            $table->decimal('total_cost', 12, 2);
            $table->string('fuel_type', 20)->nullable();
            $table->string('station', 200)->nullable();
            $table->boolean('full_refill')->default(true);
            $table->text('notes')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();

            $table->foreign('company_id')->references('id')->on('companies')->onDelete('cascade');
            $table->foreign('vehicle_id')->references('id')->on('vehicles')->onDelete('cascade');
            $table->index(['vehicle_id', 'date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vehicle_fuel_records');
    }
};
