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
        Schema::create('processor_branches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('processor_id')->constrained('payroll_payment_processors')->onDelete('cascade')->onUpdate('cascade');
            $table->foreignId('branch_id')->constrained('bank_branches')->onDelete('cascade')->onUpdate('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('processor_branches');
    }
};
