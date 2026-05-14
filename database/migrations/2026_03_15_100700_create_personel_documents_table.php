<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('personel_documents', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('personel_id');
            $table->string('type', 100);
            $table->string('file_path');
            $table->string('original_name')->nullable();
            $table->string('mime', 100)->nullable();
            $table->unsignedInteger('file_size')->nullable();
            $table->json('metadata')->nullable();
            $table->date('issued_at')->nullable();
            $table->date('expiry_at')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();

            $table->foreign('personel_id')->references('id')->on('personels')->onDelete('cascade');
            $table->index(['personel_id', 'type']);
        });
    }

    public function down(): void { Schema::dropIfExists('personel_documents'); }
};
