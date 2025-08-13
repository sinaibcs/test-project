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
        Schema::table('payroll_payment_processors', function (Blueprint $table) {
            $table->string('focal_email_address',255)->nullable()->change();
            $table->string('focal_phone_no')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payroll_payment_processors', function (Blueprint $table) {
            $table->string('focal_email_address',80)->nullable(false)->change();
            $table->string('focal_phone_no')->nullable(false)->change();
        });
    }
};
