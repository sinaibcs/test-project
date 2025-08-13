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

        Schema::table('emergency_payroll_payment_cycle_details', function (Blueprint $table) {
            $table->unsignedBigInteger('status_id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('emergency_payroll_payment_cycle_details', function (Blueprint $table) {
            //
        });
    }
};