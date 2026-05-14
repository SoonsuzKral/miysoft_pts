<?php
// PII: document_no_enc şifrelidir
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('visitors', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id')->index();
            $table->string('name');
            $table->string('visitor_company')->nullable();
            $table->string('phone', 20)->nullable();
            $table->string('email')->nullable();
            $table->unsignedBigInteger('host_personel_id')->nullable();
            $table->text('document_no_enc')->nullable();
            $table->string('document_type', 50)->nullable();
            $table->timestamp('visit_date');
            $table->timestamp('checkin_at')->nullable();
            $table->timestamp('checkout_at')->nullable();
            $table->boolean('badge_printed')->default(false);
            $table->text('purpose')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();

            $table->foreign('company_id')->references('id')->on('companies')->onDelete('cascade');
            $table->foreign('host_personel_id')->references('id')->on('personels')->nullOnDelete();
            $table->index(['company_id', 'visit_date']);
        });
    }

    public function down(): void { Schema::dropIfExists('visitors'); }
};
