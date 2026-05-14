<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shift_assignments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('shift_plan_id');
            $table->unsignedBigInteger('personel_id');
            $table->unsignedBigInteger('shift_id')->nullable();
            $table->date('date')->index();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();

            $table->foreign('shift_plan_id')->references('id')->on('shift_plans')->onDelete('cascade');
            $table->foreign('personel_id')->references('id')->on('personels')->onDelete('cascade');
            $table->foreign('shift_id')->references('id')->on('shifts')->nullOnDelete();
            $table->index(['personel_id', 'date']);
        });
    }

    public function down(): void { Schema::dropIfExists('shift_assignments'); }
};
