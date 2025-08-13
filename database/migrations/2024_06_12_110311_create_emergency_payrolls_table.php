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
        Schema::create('emergency_payrolls', function (Blueprint $table) {
            $table->id();
            // relations
            $table->foreignId('program_id')->constrained('allowance_programs')->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('financial_year_id')->constrained('financial_years')->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('office_id')->nullable()->comment('Payroll generating office')->constrained('offices')->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('emergency_allotment_id')->constrained('emergency_allotments')->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('installment_schedule_id')->constrained('payroll_installment_schedules')->cascadeOnUpdate()->cascadeOnDelete();
            // allowance summary
            $table->integer('total_beneficiaries')->default(0);
            $table->double('sub_total_amount', 8, 2)->default(0);
            $table->double('total_charge', 6, 2)->default(0);
            $table->double('total_amount', 8, 2)->default(0);
            // approval
            $table->boolean('is_approved')->default(false);
            $table->foreignId('approved_by_id')->nullable()->constrained('users')->cascadeOnUpdate()->cascadeOnDelete();
            $table->dateTime('approved_at')->nullable();
            $table->string('approved_doc')->nullable();
            $table->string('approved_note')->nullable();
            $table->boolean('is_rejected')->default(false);
            $table->foreignId('rejected_by_id')->nullable()->constrained('users')->cascadeOnUpdate()->cascadeOnDelete();
            $table->dateTime('rejected_at')->nullable();
            $table->string('rejected_doc')->nullable();
            $table->string('rejected_note')->nullable();
            $table->boolean('is_submitted')->default(false);
            $table->foreignId('submitted_by_id')->nullable()->constrained('users')->cascadeOnUpdate()->cascadeOnDelete();
            $table->dateTime('submitted_at')->nullable();
            // payment cycle
            $table->boolean('is_payment_cycle_generated')->default(false);
            $table->dateTime('payment_cycle_generated_at')->nullable();
            // meta
            $table->softDeletes();
            $table->foreignId('created_by_id')->nullable()->constrained('users')->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('updated_by_id')->nullable()->constrained('users')->cascadeOnUpdate()->cascadeOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('emergency_payrolls');
    }
};
