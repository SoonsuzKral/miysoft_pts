<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('travel_requests', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id')->index();
            $table->unsignedBigInteger('personel_id');
            $table->string('destination');
            $table->date('departure_date');
            $table->date('return_date');
            $table->text('purpose')->nullable();
            $table->string('accommodation')->nullable();
            $table->string('transportation_mode', 50)->nullable();
            $table->decimal('estimated_cost', 14, 2)->nullable();
            $table->string('currency', 3)->default('TRY');
            $table->string('status', 20)->default('pending')->index();
            $table->unsignedBigInteger('approved_by')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->string('rejection_reason')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('company_id')->references('id')->on('companies')->onDelete('cascade');
            $table->foreign('personel_id')->references('id')->on('personels')->restrictOnDelete();
            $table->index(['company_id', 'status']);
        });
    }

    public function down(): void { Schema::dropIfExists('travel_requests'); }
};
