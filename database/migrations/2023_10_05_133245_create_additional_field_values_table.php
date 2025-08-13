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
        Schema::create('additional_field_values', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('additional_field_id')->unsigned()->index();
            $table->foreign('additional_field_id')->references('id')->on('additional_fields')->onDelete('cascade');
            $table->string('value',120)->nullable();
            $table->timestamps();
           
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('additional_field_values');
    }
};
