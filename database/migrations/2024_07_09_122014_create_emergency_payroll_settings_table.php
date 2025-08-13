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
        Schema::create('emergency_payroll_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('allotment_id')->constrained("emergency_allotments")->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('financial_year_id')->constrained("financial_years")->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('installment_schedule_id')->constrained("payroll_installment_schedules")->cascadeOnUpdate()->cascadeOnDelete();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('emergency_payroll_settings');
    }
};
