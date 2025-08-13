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
        Schema::create('training_program_participants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('training_circular_id')->constrained()->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('training_program_id')->constrained()->cascadeOnUpdate()->cascadeOnDelete();
            $table->string('passcode',10);
            $table->json('exam_response')->nullable();
            $table->json('trainer_rating_response')->nullable();
            $table->boolean('status')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('training_program_participants');
    }
};
