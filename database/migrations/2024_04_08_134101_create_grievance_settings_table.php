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
        Schema::create('grievance_settings', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('grievance_type_id')->unsigned()->index();
            $table->bigInteger('grievance_subject_id')->unsigned()->index();
            $table->integer('first_tire_officer')->nullable();
            $table->string('first_tire_solution_time')->nullable();
            $table->integer('secound_tire_officer')->nullable();
            $table->string('secound_tire_solution_time')->nullable();
            $table->integer('third_tire_officer')->nullable();
            $table->string('third_tire_solution_time')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('grievance_settings');
    }
};