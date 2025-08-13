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
        Schema::create('payroll_installment_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('program_id')->constrained("allowance_programs")->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('financial_year_id')->constrained("financial_years")->cascadeOnUpdate()->cascadeOnDelete();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payroll_installment_settings');
    }
};
