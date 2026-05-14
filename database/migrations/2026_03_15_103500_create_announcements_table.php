<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('announcements', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id')->index();
            $table->string('title');
            $table->longText('content');
            $table->string('type', 50)->default('general');
            $table->enum('visibility', ['all', 'department', 'position', 'selected'])->default('all');
            $table->json('target_audience')->nullable();
            $table->boolean('is_pinned')->default(false);
            $table->boolean('is_published')->default(false)->index();
            $table->timestamp('publish_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->string('attachment')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('company_id')->references('id')->on('companies')->onDelete('cascade');
        });
    }

    public function down(): void { Schema::dropIfExists('announcements'); }
};
