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
        Schema::create('training_participants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('training_circular_id')->constrained()->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('training_program_id')->constrained()->cascadeOnUpdate()->cascadeOnDelete();
            $table->unsignedInteger('roll_no')->nullable();
            $table->date('birth_date')->nullable();
            $table->unsignedMediumInteger('organization_id')->nullable()->comment('lookups');
            $table->string('designation')->nullable();
            $table->string('document')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('training_participants');
    }
};
