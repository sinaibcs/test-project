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
        Schema::create('application_allowance_values', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('application_id')->unsigned()->index();
            $table->foreign('application_id')->references('id')->on('applications')->onDelete('cascade');

            $table->bigInteger('allow_addi_fields_id')->unsigned()->index();
            $table->foreign('allow_addi_fields_id')->references('id')->on('additional_fields')->onDelete('cascade');

            $table->bigInteger('allow_addi_field_values_id')->unsigned()->index()->nullable();
            $table->foreign('allow_addi_field_values_id')->references('id')->on('additional_field_values')->onDelete('cascade');
            $table->string('value', 255)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('application_allowance_values');
    }
};
