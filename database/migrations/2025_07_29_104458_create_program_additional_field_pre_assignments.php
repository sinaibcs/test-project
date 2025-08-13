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
        Schema::create('program_additional_field_pre_assignments', function (Blueprint $table) {
            $table->foreignId('allowance_program_id')->cascadeOnDelete()->cascadeOnUpdate();
            $table->foreignId('additional_field_id')->cascadeOnDelete()->cascadeOnUpdate();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('program_additional_field_pre_assignments');
    }
};
