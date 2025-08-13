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
            $table->enum('status', ['Pending', 'Initiated', 'Partially Completed', 'Completed', 'Rejected'])->default('Pending');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('emergency_payroll_payment_cycle_details', function (Blueprint $table) {
            $table->dropIfExists('status');
        });
    }
};
