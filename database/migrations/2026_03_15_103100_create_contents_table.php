<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contents', function (Blueprint $table) {
            $table->id();
            $table->string('key', 100)->unique()->index();
            $table->string('section', 100)->nullable()->index();
            $table->string('label')->nullable();
            $table->longText('value')->nullable();
            $table->string('type', 50)->default('text');
            $table->json('meta')->nullable();
            $table->boolean('is_active')->default(true);
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void { Schema::dropIfExists('contents'); }
};
