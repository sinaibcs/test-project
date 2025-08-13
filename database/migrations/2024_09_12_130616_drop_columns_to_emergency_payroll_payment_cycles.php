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
        Schema::table('emergency_payroll_payment_cycles', function (Blueprint $table) {
            $table->dropColumn('total_charge');
            $table->dropColumn('sub_total_amount');
            $table->dropColumn('total_amount');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('emergency_payroll_payment_cycles', function (Blueprint $table) {
            //
        });
    }
};
