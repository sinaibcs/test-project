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
        Schema::create('allowance_program_ages', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('allowance_program_id')->unsigned();
            $table->integer('gender_id')->nullable();
            $table->bigInteger('min_age')->nullable();
            $table->bigInteger('max_age')->nullable();
            $table->double('amount', 8,2)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('allowance_program_ages');
    }
};
