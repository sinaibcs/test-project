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
        Schema::table('emergency_allotments', function (Blueprint $table) {
            DB::statement("ALTER TABLE emergency_allotments MODIFY no_of_existing_benificiariy INT NULL");
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('emergency_allotments', function (Blueprint $table) {
            DB::statement("ALTER TABLE emergency_allotments MODIFY no_of_existing_benificiariy INT NOT NULL DEFAULT 0");
        });
    }
};
