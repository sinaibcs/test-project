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
            $table->string('returned_remarks')->nullable();
            $table->smallInteger('returned_code')->nullable();
            $table->string('returned_text')->nullable();
            $table->string('eft_reference_number')->nullable();
            $table->string('payment_uid')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payroll_payment_cycle_details', function (Blueprint $table) {
            $table->dropColumn(['returned_remarks','returned_code','returned_text','eft_reference_number','payment_uid']);
        });
    }
};
