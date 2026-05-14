<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shift_swap_requests', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id')->index();
            $table->unsignedBigInteger('requester_id');
            $table->unsignedBigInteger('target_personel_id');
            $table->date('requester_date');
            $table->date('target_date');
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->text('note')->nullable();
            $table->timestamps();

            $table->foreign('company_id')->references('id')->on('companies')->onDelete('cascade');
            $table->foreign('requester_id')->references('id')->on('personels')->onDelete('cascade');
            $table->foreign('target_personel_id')->references('id')->on('personels')->onDelete('cascade');
        });
    }

    public function down(): void { Schema::dropIfExists('shift_swap_requests'); }
};
