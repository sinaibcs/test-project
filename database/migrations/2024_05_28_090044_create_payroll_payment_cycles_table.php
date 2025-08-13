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
        Schema::create('payroll_payment_cycles', function (Blueprint $table) {
            $table->id();
            $table->string('cycle_id')->unique();
            $table->string('name_en');
            $table->string('name_bn');

            // allowance summary
            $table->integer('total_beneficiaries')->default(0);
            $table->double('sub_total_amount', 8, 2)->default(0);
            $table->double('total_charge', 6, 2)->default(0);
            $table->double('total_amount', 8, 2)->default(0);

            $table->softDeletes();
            $table->foreignId('created_by_id')->nullable()->constrained('users')->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('updated_by_id')->nullable()->constrained('users')->cascadeOnUpdate()->cascadeOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payroll_payment_cycles');
    }
};
