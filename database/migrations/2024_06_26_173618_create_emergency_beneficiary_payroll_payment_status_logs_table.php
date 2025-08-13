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
        Schema::create('emergency_beneficiary_payroll_payment_status_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('program_id')->nullable()->constrained('allowance_programs', 'id', 'fk_program_id')->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('installment_schedule_id')->nullable()->constrained('payroll_installment_schedules', 'id', 'fk_installment_schedule_id')->cascadeOnUpdate()->cascadeOnDelete();
            $table->unsignedBigInteger('financial_year_id')->nullable();
            $table->foreignId('emergency_beneficiary_id')->nullable()->constrained('emergency_beneficiaries', 'id', 'fk_emergency_beneficiarie_id')->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('emergency_payroll_id')->nullable()->constrained('emergency_payrolls', 'id', 'fk_emergency_payroll_id')->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('emergency_payroll_details_id')->nullable()->constrained('emergency_payroll_details', 'id', 'fk_emergency_payroll_details_id')->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('emergency_payment_cycle_id')->nullable()->constrained('emergency_payroll_payment_cycles', 'id', 'fk_emergency_cycle_id')->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('emergency_payment_cycle_details_id')->nullable()->constrained('emergency_payroll_payment_cycle_details', 'id', 'fk_emergency_cycle_details_id')->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('status_id')->constrained('payroll_payment_statuses', 'id', 'fk_payroll_payment_status_id')->cascadeOnUpdate()->cascadeOnDelete();
            $table->integer('created_by')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('emergency_beneficiary_payroll_payment_status_logs');
    }
};