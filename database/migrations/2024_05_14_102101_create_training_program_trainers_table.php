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
        Schema::create('training_program_trainers', function (Blueprint $table) {
            $table->foreignId('training_program_id')->constrained()->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('trainer_id')->constrained('lookups')->cascadeOnUpdate()->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('training_program_trainers');
    }
};
