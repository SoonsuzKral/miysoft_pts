<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('personels', function (Blueprint $table) {
            $table->boolean('is_special_hours')->default(false)->after('is_active');
        });
    }

    public function down(): void
    {
        Schema::table('personels', function (Blueprint $table) {
            $table->dropColumn('is_special_hours');
        });
    }
};
