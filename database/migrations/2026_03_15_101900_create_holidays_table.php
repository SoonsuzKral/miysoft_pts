<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('holidays', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id')->nullable()->index();
            $table->string('country_code', 5)->default('TR');
            $table->string('region', 100)->nullable();
            $table->string('name');
            $table->date('date')->index();
            $table->json('recurrence_rule')->nullable();
            $table->boolean('is_national')->default(true);
            $table->timestamps();

            $table->foreign('company_id')->references('id')->on('companies')->nullOnDelete();
            $table->index(['company_id', 'date']);
        });
    }

    public function down(): void { Schema::dropIfExists('holidays'); }
};
