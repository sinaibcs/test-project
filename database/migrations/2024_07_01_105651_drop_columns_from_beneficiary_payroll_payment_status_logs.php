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
        // DB::statement('ALTER TABLE beneficiary_payroll_payment_status_logs DROP FOREIGN KEY fk_program');
        // DB::statement('ALTER TABLE beneficiary_payroll_payment_status_logs DROP FOREIGN KEY fk_installment_schedule');
        // DB::statement('ALTER TABLE beneficiary_payroll_payment_status_logs DROP FOREIGN KEY fk_payroll');
        // DB::statement('ALTER TABLE beneficiary_payroll_payment_status_logs DROP FOREIGN KEY fk_payment_cycle');

        // // Drop columns using Schema builder
        // Schema::table('beneficiary_payroll_payment_status_logs', function (Blueprint $table) {
        //     $table->dropColumn('program_id');
        //     $table->dropColumn('installment_schedule_id');
        //     $table->dropColumn('financial_year_id');
        //     $table->dropColumn('payroll_id');
        //     $table->dropColumn('payment_cycle_id');
        // });
        Schema::table('beneficiary_payroll_payment_status_logs', function (Blueprint $table) {
            $table->dropForeign('fk_program');
            $table->dropForeign('fk_installment_schedule');
            $table->dropForeign('fk_payroll');
            $table->dropForeign('fk_payment_cycle');

            // Drop columns
            $table->dropColumn('program_id');
            $table->dropColumn('installment_schedule_id');
            $table->dropColumn('financial_year_id');
            $table->dropColumn('payroll_id');
            $table->dropColumn('payment_cycle_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('beneficiary_payroll_payment_status_logs', function (Blueprint $table) {
            $table->foreignId('program_id')->nullable()->constrained('allowance_programs', 'id', 'fk_program')->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('installment_schedule_id')->nullable()->constrained('payroll_installment_schedules', 'id', 'fk_installment_schedule')->cascadeOnUpdate()->cascadeOnDelete();
            $table->unsignedBigInteger('financial_year_id')->nullable();
            $table->foreignId('payroll_id')->nullable()->constrained('payrolls', 'id', 'fk_payroll')->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('payment_cycle_id')->nullable()->constrained('payroll_payment_cycles', 'id', 'fk_payment_cycle')->cascadeOnUpdate()->cascadeOnDelete();
        });
    }
};