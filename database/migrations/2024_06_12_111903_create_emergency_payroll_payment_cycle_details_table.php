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
        Schema::create('emergency_payroll_payment_cycle_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('emergency_cycle_id')->constrained('emergency_payroll_payment_cycles', 'id', 'fk_emergency_cycle')->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('emergency_payroll_id')->constrained('emergency_payrolls', 'id', 'fk_emergency_payroll')->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('emergency_payroll_detail_id')->constrained('emergency_payroll_details', 'id', 'fk_emergency_payroll_detail')->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('emergency_beneficiary_id')->constrained('emergency_beneficiaries', 'id', 'fk_emergency_beneficiary')->cascadeOnUpdate()->cascadeOnDelete();
            // allowance summary
            $table->double('total_amount', 8, 2)->default(0);
            // payroll amount
            $table->double('amount', 8, 2);
            $table->double('charge', 6, 2);
            // payment cycle status
            $table->enum('status', ['Pending', 'Initiated', 'Completed', 'Failed','Re-Submitted'])->default('Pending');
            $table->softDeletes();
            $table->foreignId('updated_by_id')->nullable()->constrained('users')->cascadeOnUpdate()->cascadeOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('emergency_payroll_payment_cycle_details');
    }
};
