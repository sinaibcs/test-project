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
            $table->boolean('is_payment_initiated')->nullable()->default(false);
            $table->dateTime('payment_initiated_at')->nullable();
            $table->boolean('is_payment_disbursed')->nullable()->default(false);
            $table->dateTime('payment_disbursed_at')->nullable();
            $table->integer('account_type')->nullable();
            $table->string('account_name')->nullable();
            $table->string('account_number')->nullable();
            $table->string('account_owner')->nullable();
            $table->string('bank_name')->nullable();
            $table->string('mfs_name')->nullable();
            $table->string('branch_name')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payroll_payment_cycle_details', function (Blueprint $table) {
            $table->dropColumn('is_payment_initiated');
            $table->dropColumn('payment_initiated_at');
            $table->dropColumn('is_payment_disbursed');
            $table->dropColumn('payment_disbursed_at');
            $table->dropColumn('account_type');
            $table->dropColumn('account_name');
            $table->dropColumn('account_number');
            $table->dropColumn('account_owner');
            $table->dropColumn('bank_name');
            $table->dropColumn('mfs_name');
            $table->dropColumn('branch_name');
        });
    }
};
