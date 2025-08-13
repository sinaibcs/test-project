<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('payroll_payment_cycle_details', function (Blueprint $table) {
           $table->foreignId('financial_year_id')->nullable()->constrained('financial_years', 'id')->cascadeOnUpdate()->cascadeOnDelete();
           $table->foreignId('installment_schedule_id')->nullable()->constrained('payroll_installment_schedules', 'id')->cascadeOnUpdate()->cascadeOnDelete();
           $table->foreignId('program_id')->nullable()->constrained('allowance_programs', 'id')->cascadeOnUpdate()->cascadeOnDelete();

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payroll_payment_cycle_details', function (Blueprint $table) {
            $table->dropForeign(['financial_year_id']);
            $table->dropColumn('financial_year_id');
            $table->dropForeign(['installment_schedule_id']);
            $table->dropColumn('installment_schedule_id');
            $table->dropForeign(['program_id']);
            $table->dropColumn('program_id');

        });
    }
};