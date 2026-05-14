<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('advance_requests', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id')->index();
            $table->unsignedBigInteger('personel_id');
            $table->decimal('amount', 12, 2);
            $table->string('currency', 3)->default('TRY');
            $table->text('reason');
            $table->enum('status', ['pending', 'approved', 'rejected', 'cancelled', 'repaid'])->default('pending')->index();
            $table->json('repayment_plan')->nullable();
            $table->unsignedBigInteger('approved_by')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->string('rejection_reason')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('company_id')->references('id')->on('companies')->onDelete('cascade');
            $table->foreign('personel_id')->references('id')->on('personels')->onDelete('cascade');
            $table->index(['company_id', 'status']);
        });
    }

    public function down(): void { Schema::dropIfExists('advance_requests'); }
};
