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
        Schema::create('allotment_extra_beneficiaries', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('allotment_id')->unsigned();
            $table->foreign('allotment_id')->references('id')->on('allotments')->onDelete('cascade');
            $table->integer('gender_id')->unsigned();
            $table->integer('beneficiary_additional');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('allotment_extra_beneficiaries');
    }
};
