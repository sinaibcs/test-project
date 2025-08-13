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
        Schema::create('office_has_wards', function (Blueprint $table) {
            $table->id();
            
            $table->bigInteger('office_id')->unsigned()->index()->nullable();
            $table->foreign('office_id')->references('id')->on('offices')->onDelete('cascade');
            
            
            $table->bigInteger('ward_id')->unsigned()->index()->nullable();
            $table->foreign('ward_id')->references('id')->on('locations')->onDelete('cascade');
            
            $table->timestamps();
            $table->softDeletes();
            
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('office_has_wards');
    }
};
