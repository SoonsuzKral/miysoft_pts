<?php
// 1 file = 1 operation — date: 2026-03-15

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->unsignedBigInteger('company_id')->nullable()->after('id')->index();
            $table->boolean('is_active')->default(true)->after('remember_token');
            $table->datetime('last_login_at')->nullable()->after('is_active');
            $table->boolean('two_factor_enabled')->default(false)->after('last_login_at');
            $table->json('settings')->nullable()->after('two_factor_enabled');
            $table->foreign('company_id')->references('id')->on('companies')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['company_id']);
            $table->dropColumn(['company_id', 'is_active', 'last_login_at', 'two_factor_enabled', 'settings']);
        });
    }
};
