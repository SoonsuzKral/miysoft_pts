<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shift_attendances', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id')->index();
            $table->unsignedBigInteger('shift_assignment_id')->nullable();
            $table->unsignedBigInteger('personel_id');
            $table->unsignedBigInteger('shift_id')->nullable();
            $table->date('date')->index();
            $table->dateTime('clock_in')->nullable();
            $table->dateTime('clock_out')->nullable();
            $table->string('status', 20)->default('pending');
            $table->integer('late_minutes')->default(0);
            $table->integer('early_leave_minutes')->default(0);
            $table->text('note')->nullable();
            $table->string('clock_in_source', 20)->nullable();
            $table->string('clock_out_source', 20)->nullable();
            $table->unsignedBigInteger('clocked_in_by')->nullable();
            $table->unsignedBigInteger('clocked_out_by')->nullable();
            $table->timestamps();

            $table->foreign('company_id')->references('id')->on('companies')->onDelete('cascade');
            $table->foreign('shift_assignment_id')->references('id')->on('shift_assignments')->nullOnDelete();
            $table->foreign('personel_id')->references('id')->on('personels')->onDelete('cascade');
            $table->foreign('shift_id')->references('id')->on('shifts')->nullOnDelete();
            $table->foreign('clocked_in_by')->references('id')->on('users')->nullOnDelete();
            $table->foreign('clocked_out_by')->references('id')->on('users')->nullOnDelete();
            $table->unique(['personel_id', 'date']);
        });
    }

    public function down(): void { Schema::dropIfExists('shift_attendances'); }
};
