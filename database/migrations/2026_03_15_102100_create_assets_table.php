<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('assets', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id')->index();
            $table->unsignedBigInteger('asset_type_id');
            $table->string('name');
            $table->string('serial', 100)->nullable()->index();
            $table->string('barcode', 100)->nullable()->index();
            $table->date('purchase_date')->nullable();
            $table->date('warranty_end')->nullable();
            $table->decimal('price', 12, 2)->nullable();
            $table->enum('status', ['available', 'assigned', 'maintenance', 'retired'])->default('available')->index();
            $table->string('location')->nullable();
            $table->unsignedBigInteger('assigned_to')->nullable();
            $table->json('custom_attributes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('company_id')->references('id')->on('companies')->onDelete('cascade');
            $table->foreign('asset_type_id')->references('id')->on('asset_types')->onDelete('cascade');
            $table->foreign('assigned_to')->references('id')->on('personels')->nullOnDelete();
            $table->index(['company_id', 'status']);
        });
    }

    public function down(): void { Schema::dropIfExists('assets'); }
};
