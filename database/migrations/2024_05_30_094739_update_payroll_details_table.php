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
        Schema::table('payroll_details', function (Blueprint $table) {
            $table->dropColumn('mobile');
            $table->dropColumn('account_type');
            $table->dropColumn('bank_name');
            $table->dropColumn('branch_name');
            $table->dropColumn('account_name');
            $table->dropColumn('account_number');
            $table->dropColumn('account_owner');
            $table->dropColumn('is_payment_processed');
            $table->dropColumn('payment_processed_at');
            // update
            $table->foreignId('updated_by_id')->nullable()->constrained('users')->cascadeOnUpdate()->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payroll_details', function (Blueprint $table) {
            //
        });
    }
};
