<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('committees', function (Blueprint $table) {
            $table->foreignId('division_id')->nullable()->constrained('locations')->cascadeOnDelete()->cascadeOnUpdate();
            $table->foreignId('district_id')->nullable()->constrained('locations')->cascadeOnDelete()->cascadeOnUpdate();
            $table->foreignId('location_type_id')->nullable()->constrained('lookups')->cascadeOnDelete()->cascadeOnUpdate();
            $table->foreignId('city_corp_id')->nullable()->constrained('locations')->cascadeOnDelete()->cascadeOnUpdate();
            $table->foreignId('district_pourashava_id')->nullable()->constrained('locations')->cascadeOnDelete()->cascadeOnUpdate();
            $table->foreignId('upazila_id')->nullable()->constrained('locations')->cascadeOnDelete()->cascadeOnUpdate();
            $table->foreignId('sub_location_type_id')->nullable()->constrained('lookups')->cascadeOnDelete()->cascadeOnUpdate();
            $table->foreignId('pourashava_id')->nullable()->constrained('locations')->cascadeOnDelete()->cascadeOnUpdate();
            $table->foreignId('thana_id')->nullable()->constrained('locations')->cascadeOnDelete()->cascadeOnUpdate();
            $table->foreignId('union_id')->nullable()->constrained('locations')->cascadeOnDelete()->cascadeOnUpdate();
            $table->foreignId('ward_id')->nullable()->constrained('locations')->cascadeOnDelete()->cascadeOnUpdate();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('committees', function (Blueprint $table) {
            //
        });
    }
};
