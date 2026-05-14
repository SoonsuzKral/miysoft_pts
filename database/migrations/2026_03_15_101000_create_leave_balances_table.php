<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('leave_balances', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('personel_id');
            $table->unsignedBigInteger('leave_type_id');
            $table->year('year');
            $table->decimal('entitled_days', 5, 2)->default(0);
            $table->decimal('used_days', 5, 2)->default(0);
            $table->decimal('remaining_days', 5, 2)->default(0);
            $table->timestamps();

            $table->foreign('personel_id')->references('id')->on('personels')->onDelete('cascade');
            $table->foreign('leave_type_id')->references('id')->on('leave_types')->onDelete('cascade');
            $table->unique(['personel_id', 'leave_type_id', 'year']);
            $table->index(['personel_id', 'year']);
        });
    }

    public function down(): void { Schema::dropIfExists('leave_balances'); }
};
