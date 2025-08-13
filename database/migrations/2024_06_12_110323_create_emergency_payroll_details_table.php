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
        Schema::create('emergency_payroll_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('emergency_payroll_id')->constrained('emergency_payrolls')->cascadeOnUpdate()->cascadeOnDelete();
            // beneficiary info
            $table->foreignId('emergency_beneficiary_id')->constrained('emergency_beneficiaries')->cascadeOnUpdate()->cascadeOnDelete();
            // payroll amount
            $table->double('amount', 8, 2);
            $table->double('charge', 6, 2);
            $table->double('total_amount', 8, 2);
            // approval status
            $table->enum('status', ['Pending', 'Approved', 'Rejected'])->default('Pending');
            $table->softDeletes();
            $table->timestamps();
            $table->foreignId('updated_by_id')->nullable()->constrained('users')->cascadeOnUpdate()->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('emergency_payroll_details');
    }
};
