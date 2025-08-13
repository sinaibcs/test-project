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
        Schema::create('online_application_disabled_locations', function (Blueprint $table) {
            // $table->id();
            $table->unsignedBigInteger('allowance_program_id');
            $table->unsignedBigInteger('location_id');
        
            $table->foreign('allowance_program_id', 'fk_app_disabled_allowance')
                  ->references('id')
                  ->on('allowance_programs')
                  ->onUpdate('cascade')
                  ->onDelete('cascade');
        
            $table->foreign('location_id', 'fk_app_disabled_location')
                  ->references('id')
                  ->on('locations')
                  ->onUpdate('cascade')
                  ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('online_application_disabled_locations');
    }
};
