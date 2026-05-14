<?php
// BÜYÜK TABLO: MySQL partition by RANGE(YEAR(recorded_at)) önerilir.
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('time_records', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id')->index();
            $table->unsignedBigInteger('personel_id');
            $table->enum('type', ['in', 'out', 'break_start', 'break_end']);
            $table->dateTime('recorded_at');
            $table->enum('source', ['web', 'mobile', 'biometric', 'manual'])->default('web');
            $table->json('geo')->nullable();
            $table->text('note')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();

            $table->foreign('company_id')->references('id')->on('companies')->onDelete('cascade');
            $table->foreign('personel_id')->references('id')->on('personels')->onDelete('cascade');
            $table->index(['personel_id', 'recorded_at']);
            $table->index(['company_id', 'recorded_at']);
        });
    }

    public function down(): void { Schema::dropIfExists('time_records'); }
};
