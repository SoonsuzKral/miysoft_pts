<?php
// PII NOT: national_id_enc uygulama seviyesinde şifrelidir.
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('personels', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id')->index();
            $table->string('first_name');
            $table->string('last_name');
            $table->string('email')->nullable()->index();
            $table->string('phone', 20)->nullable();
            $table->text('national_id_enc')->nullable();
            $table->string('national_id_hash', 64)->nullable()->index();
            $table->date('birth_date')->nullable();
            $table->enum('gender', ['M', 'F', 'other'])->nullable();
            $table->string('blood_type', 5)->nullable();
            $table->unsignedBigInteger('position_id')->nullable();
            $table->unsignedBigInteger('department_id')->nullable();
            $table->decimal('salary', 14, 2)->nullable();
            $table->string('currency', 3)->default('TRY');
            $table->date('hire_date')->nullable();
            $table->date('termination_date')->nullable();
            $table->enum('status', ['active', 'terminated', 'on_leave', 'suspended'])->default('active')->index();
            $table->json('attributes')->nullable();
            $table->boolean('is_active')->default(true)->index();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('company_id')->references('id')->on('companies')->onDelete('cascade');
            $table->foreign('position_id')->references('id')->on('positions')->nullOnDelete();
            $table->foreign('department_id')->references('id')->on('departments')->nullOnDelete();
            $table->index(['company_id', 'department_id']);
            $table->index(['company_id', 'is_active']);
        });
    }

    public function down(): void { Schema::dropIfExists('personels'); }
};
