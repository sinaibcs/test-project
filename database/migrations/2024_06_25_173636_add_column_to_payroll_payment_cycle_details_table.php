<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('payroll_payment_cycle_details', function (Blueprint $table) {
            $table->dropColumn('status');
        });

        Schema::table('payroll_payment_cycle_details', function (Blueprint $table) {
            $table->enum('status', ['Pending', 'Initiated', 'Completed', 'Failed', 'Revised', 'Re-Submitted', 'Deleted'])->default('Pending');
            $table->integer('status_id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('payroll_payment_cycle_details', function (Blueprint $table) {
            $table->dropColumn('status');
            $table->dropColumn('status_id');
        });

        Schema::table('payroll_payment_cycle_details', function (Blueprint $table) {
            $table->enum('status', ['Pending', 'Initiated', 'Completed', 'Failed'])->default('Pending');
        });
    }
};
