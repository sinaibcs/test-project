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
        Schema::table('additional_field_values', function (Blueprint $table) {
           $table->string('value_en')->nullable();
           $table->string('value_bn')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('additional_field_values', function (Blueprint $table) {
           $table->string('value_en')->nullable();
           $table->string('value_bn')->nullable();
          


        });
    }
};