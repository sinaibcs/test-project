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
        Schema::create('emergency_beneficiary_bank_info_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('beneficiary_id')->constrained('emergency_beneficiaries')->cascadeOnUpdate()->cascadeOnDelete();
            $table->string('account_name');
            $table->string('account_number');
            $table->string('account_owner');
            $table->unsignedTinyInteger('account_type')->nullable()->comment("1=Bank;2=Mobile");
            $table->string('bank_name')->nullable();
            $table->string('branch_name')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('emergency_beneficiary_bank_info_logs');
    }
};