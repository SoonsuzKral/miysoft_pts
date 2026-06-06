<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vehicles', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id')->index();
            $table->string('plate', 20);
            $table->string('brand', 100);
            $table->string('model', 100);
            $table->year('year')->nullable();
            $table->string('color', 30)->nullable();
            $table->string('vin', 50)->nullable();
            $table->string('engine_type', 30)->nullable();
            $table->string('fuel_type', 30)->nullable();
            $table->date('acquisition_date')->nullable();
            $table->decimal('acquisition_cost', 14, 2)->nullable();
            $table->string('status', 30)->default('active')->index();
            $table->unsignedBigInteger('assigned_personel_id')->nullable();
            $table->date('last_maintenance_date')->nullable();
            $table->date('next_maintenance_date')->nullable();
            $table->text('notes')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('company_id')->references('id')->on('companies')->onDelete('cascade');
            $table->index(['company_id', 'status']);
        });
    }

    public function down(): void { Schema::dropIfExists('vehicles'); }
};
