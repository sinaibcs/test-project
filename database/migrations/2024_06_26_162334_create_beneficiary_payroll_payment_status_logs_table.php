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
        Schema::create('beneficiary_payroll_payment_status_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('program_id')->nullable()->constrained('allowance_programs', 'id', 'fk_program')->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('installment_schedule_id')->nullable()->constrained('payroll_installment_schedules', 'id', 'fk_installment_schedule')->cascadeOnUpdate()->cascadeOnDelete();
            $table->unsignedBigInteger('financial_year_id')->nullable();
            $table->foreignId('beneficiary_id')->nullable()->constrained('beneficiaries', 'id', 'fk_beneficiary')->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('payroll_id')->nullable()->constrained('payrolls', 'id', 'fk_payroll')->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('payroll_details_id')->nullable()->constrained('payroll_details', 'id', 'fk_payroll_details')->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('payment_cycle_id')->nullable()->constrained('payroll_payment_cycles', 'id', 'fk_payment_cycle')->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('payment_cycle_details_id')->nullable()->constrained('payroll_payment_cycle_details', 'id', 'fk_cycle_details')->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('status_id')->constrained('payroll_payment_statuses', 'id', 'fk_payroll_payment_status')->cascadeOnUpdate()->cascadeOnDelete();
            $table->integer('created_by')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('beneficiary_payroll_payment_status_logs');
    }
};
