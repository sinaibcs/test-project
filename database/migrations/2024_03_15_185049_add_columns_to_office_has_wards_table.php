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
        Schema::table('office_has_wards', function (Blueprint $table) {
            $table->foreignId('division_id')->nullable();
            $table->foreignId('district_id')->nullable();
            $table->foreignId('city_id')->nullable();
            $table->foreignId('thana_id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('office_has_wards', function (Blueprint $table) {
            $table->dropColumn('division_id', 'district_id', 'city_id', 'thana_id');
        });
    }
};
