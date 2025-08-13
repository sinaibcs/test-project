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
        Schema::create('payroll_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payroll_id')->constrained('payrolls')->cascadeOnUpdate()->cascadeOnDelete();
            // beneficiary info
            $table->foreignId('beneficiary_id')->constrained('beneficiaries')->cascadeOnUpdate()->cascadeOnDelete();
            $table->string('mobile')->nullable();
            $table->unsignedTinyInteger('account_type')->nullable()->comment("1=Bank;2=Mobile");
            $table->string('bank_name')->nullable();
            $table->string('branch_name')->nullable();
            $table->string('account_name');
            $table->string('account_number');
            $table->string('account_owner');
            // payroll amount
            $table->double('amount', 8, 2);
            $table->double('charge', 6, 2);
            $table->double('total_amount', 8, 2);
            // approval status
            $table->enum('status', ['Pending', 'Approved', 'Rejected'])->default('Pending');
            // payment lifecycle
            $table->boolean('is_payment_processed')->comment('Is iBAS++ processed?')->default(false);
            $table->dateTime('payment_processed_at')->nullable();
            // meta
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payroll_details');
    }
};
