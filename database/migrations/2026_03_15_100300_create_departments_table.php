<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('departments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id')->index();
            $table->unsignedBigInteger('parent_department_id')->nullable()->index();
            $table->string('name');
            $table->string('code', 50)->nullable();
            $table->unsignedBigInteger('manager_id')->nullable();
            $table->json('metadata')->nullable();
            $table->boolean('is_active')->default(true);
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('company_id')->references('id')->on('companies')->onDelete('cascade');
            $table->foreign('parent_department_id')->references('id')->on('departments')->nullOnDelete();
            $table->index(['company_id', 'is_active']);
        });
    }

    public function down(): void { Schema::dropIfExists('departments'); }
};
