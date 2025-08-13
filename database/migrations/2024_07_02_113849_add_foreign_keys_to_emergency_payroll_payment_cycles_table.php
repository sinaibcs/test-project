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
        Schema::disableForeignKeyConstraints();

        Schema::table('emergency_payroll_payment_cycles', function (Blueprint $table) {
            $table->foreignId('emergency_payroll_id')->after('id')->constrained('emergency_payrolls')->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('financial_year_id')->after('emergency_payroll_id')->constrained('financial_years')->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('installment_schedule_id')->after('financial_year_id')->constrained('payroll_installment_schedules')->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('program_id')->after('installment_schedule_id')->constrained('allowance_programs')->cascadeOnUpdate()->cascadeOnDelete();
        });

        Schema::enableForeignKeyConstraints();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('emergency_payroll_payment_cycles', function (Blueprint $table) {
            $table->dropForeign(['emergency_payroll_id']);
            $table->dropColumn('emergency_payroll_id');

            $table->dropForeign(['financial_year_id']);
            $table->dropColumn('financial_year_id');

            $table->dropForeign(['installment_schedule_id']);
            $table->dropColumn('installment_schedule_id');

            $table->dropForeign(['program_id']);
            $table->dropColumn('program_id');
        });
    }
};
