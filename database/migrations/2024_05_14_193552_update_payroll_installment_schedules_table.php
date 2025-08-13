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
        Schema::table('payroll_installment_schedules', function (Blueprint $table) {
            $table->dropForeign('payroll_installment_schedules_installment_setting_id_foreign');
            $table->dropColumn('installment_setting_id');
            $table->dropColumn('installment_start_date');
            $table->dropColumn('installment_end_date');
            $table->integer('installment_number')->nullable()->change();
            $table->renameColumn('installment_title', 'installment_name');
            $table->foreignId('program_id')->constrained("allowance_programs")->cascadeOnUpdate()->cascadeOnDelete();
            $table->string('payment_cycle', '50');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payroll_installment_schedules', function (Blueprint $table) {
            //
        });
    }
};
