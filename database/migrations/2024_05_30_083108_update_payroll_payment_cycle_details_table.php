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
        Schema::table('payroll_payment_cycle_details', function (Blueprint $table) {
            // drop column
            $table->dropColumn('total_beneficiaries');
            $table->dropColumn('sub_total_amount');
            $table->dropColumn('total_charge');
            // add column
            $table->foreignId('payroll_detail_id')->constrained('payroll_details')->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('beneficiary_id')->constrained('beneficiaries')->cascadeOnUpdate()->cascadeOnDelete();
            // payroll amount
            $table->double('amount', 8, 2);
            $table->double('charge', 6, 2);
            // payment cycle status
            $table->enum('status', ['Pending', 'Initiated', 'Completed', 'Failed'])->default('Pending');
            // revise
            $table->softDeletes();
            $table->foreignId('updated_by_id')->nullable()->constrained('users')->cascadeOnUpdate()->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payroll_payment_cycle_details', function (Blueprint $table) {
            //
        });
    }
};
