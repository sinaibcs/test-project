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
        Schema::table('payroll_payment_cycles', function (Blueprint $table) {
            // processing status
            $table->enum('status', ['Pending', 'Initiated', 'Partially Completed', 'Completed'])->default('Pending');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payroll_payment_cycles', function (Blueprint $table) {
            //
        });
    }
};
