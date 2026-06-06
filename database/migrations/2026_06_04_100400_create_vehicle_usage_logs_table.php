<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vehicle_usage_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id')->index();
            $table->unsignedBigInteger('vehicle_id');
            $table->unsignedBigInteger('personel_id')->nullable();
            $table->decimal('start_km', 12, 2)->nullable();
            $table->decimal('end_km', 12, 2)->nullable();
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->time('start_time')->nullable();
            $table->time('end_time')->nullable();
            $table->string('origin', 500)->nullable();
            $table->string('destination', 500)->nullable();
            $table->text('purpose')->nullable();
            $table->text('notes')->nullable();
            $table->string('status', 20)->default('active');
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('company_id')->references('id')->on('companies')->onDelete('cascade');
            $table->foreign('vehicle_id')->references('id')->on('vehicles')->onDelete('cascade');
            $table->index(['vehicle_id', 'start_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vehicle_usage_logs');
    }
};
