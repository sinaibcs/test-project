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
        Schema::create('payroll_payment_processors', function (Blueprint $table) {
            $table->id();
            $table->foreignId('processor_type_id')->constrained('lookups')->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('bank_id')->constrained('lookups')->cascadeOnUpdate()->cascadeOnDelete();
            $table->string('bank_branch_name', '100');
            $table->string('bank_routing_number', '80');
            $table->string('focal_email_address', '80');
            $table->string('focal_phone_no', '50');
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payroll_payment_processors');
    }
};
