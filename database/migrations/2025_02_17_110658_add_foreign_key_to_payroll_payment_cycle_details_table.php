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
        Schema::table('payroll_payment_cycle_details', function (Blueprint $table) {
            $table->dropIndex('payroll_payment_cycle_details_beneficiary_id_foreign');
            $table->foreign('beneficiary_id')->on('beneficiaries')->references('beneficiary_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payroll_payment_cycle_details', function (Blueprint $table) {
            $table->dropForeign('beneficiary_id');
            $table->index('beneficiary_id','payroll_payment_cycle_details_beneficiary_id_foreign');
        });
    }
};
