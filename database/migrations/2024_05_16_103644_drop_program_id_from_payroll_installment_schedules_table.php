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
        Schema::table('payroll_installment_schedules', function (Blueprint $table) {
            try {
                DB::statement('ALTER TABLE payroll_installment_schedules DROP FOREIGN KEY payroll_installment_schedules_program_id_foreign');
            } catch (\Exception $e) {
            }
            $table->dropColumn('program_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
