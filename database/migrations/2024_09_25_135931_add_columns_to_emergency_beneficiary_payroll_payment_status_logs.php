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
        Schema::table('emergency_beneficiary_payroll_payment_status_logs', function (Blueprint $table) {
            $table->integer('account_type')->nullable()->after('emergency_payment_cycle_details_id');
            $table->string('account_name')->nullable()->after('account_type');
            $table->string('account_number')->nullable()->after('account_name');
            $table->string('account_owner')->nullable()->after('account_number');
            $table->string('bank_name')->nullable()->after('account_owner');
            $table->string('mfs_name')->nullable()->after('bank_name');
            $table->string('branch_name')->nullable()->after('mfs_name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('emergency_beneficiary_payroll_payment_status_logs', function (Blueprint $table) {
            $table->dropColumn(['account_type', 'account_name', 'account_number', 'account_owner', 'bank_name', 'mfs_name', 'branch_name']);
        });
    }
};
