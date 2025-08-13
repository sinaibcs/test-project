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
        Schema::table('payroll_installment_settings', function (Blueprint $table) {
            $table->foreignId('installment_schedule_id')->constrained('payroll_installment_schedules')->cascadeOnUpdate()->cascadeOnDelete();
        });
        Schema::dropIfExists('payroll_active_installment');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payroll_installment_settings', function (Blueprint $table) {
            //
        });
    }
};
