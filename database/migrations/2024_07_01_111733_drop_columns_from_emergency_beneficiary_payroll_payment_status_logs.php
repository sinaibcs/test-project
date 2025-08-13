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
        // DB::statement('ALTER TABLE emergency_beneficiary_payroll_payment_status_logs DROP FOREIGN KEY fk_program_id');
        // DB::statement('ALTER TABLE emergency_beneficiary_payroll_payment_status_logs DROP FOREIGN KEY fk_installment_schedule_id');
        // DB::statement('ALTER TABLE emergency_beneficiary_payroll_payment_status_logs DROP FOREIGN KEY fk_emergency_payroll_id');
        // DB::statement('ALTER TABLE emergency_beneficiary_payroll_payment_status_logs DROP FOREIGN KEY fk_emergency_cycle_id');

        // // Drop columns using Schema builder
        // Schema::table('emergency_beneficiary_payroll_payment_status_logs', function (Blueprint $table) {
        //     $table->dropColumn('program_id');
        //     $table->dropColumn('installment_schedule_id');
        //     $table->dropColumn('financial_year_id');
        //     $table->dropColumn('emergency_payroll_id');
        //     $table->dropColumn('emergency_payment_cycle_id');
        // });
//        Schema::table('emergency_beneficiary_payroll_payment_status_logs', function (Blueprint $table) {
//            $table->dropForeign('fk_program_id');
//            $table->dropForeign('fk_installment_schedule_id');
//            $table->dropForeign('fk_emergency_payroll_id');
//            $table->dropForeign('fk_emergency_cycle_id');
//
//            // Drop columns
//            $table->dropColumn('program_id');
//            $table->dropColumn('installment_schedule_id');
//            $table->dropColumn('financial_year_id');
//            $table->dropColumn('emergency_payroll_id');
//            $table->dropColumn('emergency_payment_cycle_id');
//        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('emergency_beneficiary_payroll_payment_status_logs', function (Blueprint $table) {
            $table->foreignId('program_id')->nullable()->constrained('allowance_programs', 'id', 'fk_program_id')->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('installment_schedule_id')->nullable()->constrained('payroll_installment_schedules', 'id', 'fk_installment_schedule_id')->cascadeOnUpdate()->cascadeOnDelete();
            $table->unsignedBigInteger('financial_year_id')->nullable();
            $table->foreignId('emergency_payroll_id')->nullable()->constrained('emergency_payrolls', 'id', 'fk_emergency_payroll_id')->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('emergency_payment_cycle_id')->nullable()->constrained('emergency_payroll_payment_cycles', 'id', 'fk_emergency_cycle_id')->cascadeOnUpdate()->cascadeOnDelete();
        });
    }
};
