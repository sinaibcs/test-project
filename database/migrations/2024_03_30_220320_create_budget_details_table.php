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
        Schema::create('budget_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('budget_id')->constrained('budgets')->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('location_id')->constrained("locations")->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('office_id')->constrained("offices")->cascadeOnUpdate()->cascadeOnDelete();
            $table->bigInteger('previous_beneficiaries');
            $table->double('previous_amount');
            $table->bigInteger('current_beneficiaries');
            $table->double('current_amount');
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('budget_details');
    }
};
