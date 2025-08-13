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
        Schema::create('poverty_score_cut_offs', function (Blueprint $table) {
            $table->id();
            $table->integer('type')->nullable();
            $table->unsignedBigInteger('location_id')->nullable();
            $table->foreign('location_id')->references('id')->on('locations')->onDelete('restrict');
            $table->unsignedBigInteger('financial_year_id')->nullable();
            $table->foreign('financial_year_id')->references('id')->on('financial_years')->onDelete('restrict');
            $table->float('score');
            $table->timestamps();
            $table->timestamp('deleted_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('poverty_score_cut_offs');
    }
};
