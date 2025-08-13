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
        Schema::disableForeignKeyConstraints();
        Schema::table('payroll_payment_cycles', function (Blueprint $table) {
            $table->foreignId('payroll_id')->after('id')->constrained('payrolls')->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('financial_year_id')->after('payroll_id')->constrained('financial_years')->cascadeOnUpdate()->cascadeOnDelete();


        });
        Schema::enableForeignKeyConstraints();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payroll_payment_cycles', function (Blueprint $table) {
            $table->dropForeign(['payroll_id']);
            $table->dropColumn('payroll_id');

            $table->dropForeign(['financial_year_id']);
            $table->dropColumn('financial_year_id');
        });
    }
};
