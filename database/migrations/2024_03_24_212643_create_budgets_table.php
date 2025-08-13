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
        Schema::create('budgets', function (Blueprint $table) {
            $table->id();
            $table->string('budgetId', 100)->unique();
            $table->foreignId('program_id')->constrained("allowance_programs")->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('financial_year_id')->constrained("financial_years")->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('calculation_type')->constrained("lookups")->cascadeOnUpdate()->cascadeOnDelete();
            $table->double('previous_year_value');
            $table->double('calculation_value');
            $table->string('remarks')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('budgets');
    }
};
