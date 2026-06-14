<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('special_hours', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('personel_id')->constrained()->cascadeOnDelete();
            $table->string('type', 5); // 'in' or 'out'
            $table->time('scheduled_time');
            $table->json('days_of_week')->comment('[1=Mon,2=Tue,...,7=Sun]');
            $table->text('note')->nullable();
            $table->boolean('is_active')->default(true);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['company_id', 'is_active']);
            $table->unique(['personel_id', 'type']);
        });

        Schema::create('special_hour_password', function (Blueprint $table) {
            $table->id();
            $table->string('password_hash');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('special_hour_password');
        Schema::dropIfExists('special_hours');
    }
};
