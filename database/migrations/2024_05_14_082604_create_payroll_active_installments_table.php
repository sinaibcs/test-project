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
        Schema::create('payroll_active_installment', function (Blueprint $table) {
            $table->id();
            $table->foreignId('program_id')->constrained("allowance_programs")->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('financial_year_id')->constrained("financial_years")->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('installment_schedule_id')->constrained("payroll_installment_schedules")->cascadeOnUpdate()->cascadeOnDelete();
            $table->unique(['program_id', 'financial_year_id', 'installment_schedule_id'],'payroll_active_installments_composite_id_unique');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payroll_active_installments');
    }
};
