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
        Schema::create('payroll_payment_cycle_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payroll_payment_cycle_id')->constrained('payroll_payment_cycles')->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('payroll_id')->constrained('payrolls')->cascadeOnUpdate()->cascadeOnDelete();
            // allowance summary
            $table->integer('total_beneficiaries')->default(0);
            $table->double('sub_total_amount', 8, 2)->default(0);
            $table->double('total_charge', 6, 2)->default(0);
            $table->double('total_amount', 8, 2)->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payroll_payment_cycle_details');
    }
};
