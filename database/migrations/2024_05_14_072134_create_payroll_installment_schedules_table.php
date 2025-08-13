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
        Schema::create('payroll_installment_schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('installment_setting_id')->constrained('payroll_installment_settings')->cascadeOnUpdate()->cascadeOnDelete();
            $table->integer('installment_number');
            $table->string('installment_title', '250');
            $table->date('installment_start_date');
            $table->date('installment_end_date');
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payroll_installment_schedules');
    }
};
