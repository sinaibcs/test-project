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
        Schema::table('emergency_payroll_details', function (Blueprint $table) {
            $table->foreignId('status_id')->nullable()->constrained('payroll_payment_statuses')->cascadeOnUpdate()->cascadeOnDelete()->after('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('emergency_payroll_details', function (Blueprint $table) {
            //
        });
    }
};
