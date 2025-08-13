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
        Schema::create('training_circulars', function (Blueprint $table) {
            $table->id();
            $table->string('circular_name');
            $table->string('circular_reference_id', 20)->unique()->nullable();
            $table->foreignId('circular_type_id')->constrained('lookups')->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('training_type_id')->constrained('lookups')->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('status_id')->constrained('lookups')->cascadeOnUpdate()->cascadeOnDelete();
            $table->unsignedMediumInteger('no_of_participant')->nullable();
            $table->unsignedMediumInteger('no_of_participant_open')->nullable();
            $table->unsignedMediumInteger('no_of_participant_selected')->nullable();
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->string('class_duration', 20)->nullable();
            $table->text('description')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('training_circulars');
    }
};
