<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('payroll_payment_cycles', function (Blueprint $table) {
            $table->foreignId('program_id')->nullable()->constrained('allowance_programs')->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('installment_schedule_id')->nullable()->constrained('payroll_installment_schedules')->cascadeOnUpdate()->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payroll_payment_cycles', function (Blueprint $table) {
            //
        });
    }
};
