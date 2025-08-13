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
        Schema::table('applications', function (Blueprint $table) {
            $table->foreignId('current_location_type_id')->nullable();
            $table->foreignId('current_division_id')->nullable();
            $table->foreignId('current_district_id')->nullable();
            $table->foreignId('current_district_pourashava_id')->nullable();
            $table->foreignId('current_city_corp_id')->nullable();
            $table->foreignId('current_thana_id')->nullable();
            $table->foreignId('current_upazila_id')->nullable();
            $table->foreignId('current_union_id')->nullable();
            $table->foreignId('current_pourashava_id')->nullable();
            $table->foreignId('current_ward_id')->nullable();


            $table->foreignId('permanent_location_type_id')->nullable()
                ->constrained('lookups')->cascadeOnUpdate()->nullOnDelete();

            $table->foreignId('permanent_division_id')->nullable()
                ->constrained('locations')
                ->cascadeOnUpdate()->nullOnDelete();

            $table->foreignId('permanent_district_id')->nullable()
                ->constrained('locations')
                ->cascadeOnUpdate()->nullOnDelete();


            $table->foreignId('permanent_district_pourashava_id')->nullable()
                ->constrained('locations')
                ->cascadeOnUpdate()->nullOnDelete();


            $table->foreignId('permanent_city_corp_id')->nullable()
                ->constrained('locations')
                ->cascadeOnUpdate()->nullOnDelete();

            $table->foreignId('permanent_thana_id')->nullable()
                ->constrained('locations')
                ->cascadeOnUpdate()->nullOnDelete();


            $table->foreignId('permanent_upazila_id')->nullable()
                ->constrained('locations')
                ->cascadeOnUpdate()->nullOnDelete();

            $table->foreignId('permanent_union_id')->nullable()
                ->constrained('locations')
                ->cascadeOnUpdate()->nullOnDelete();

            $table->foreignId('permanent_pourashava_id')->nullable()
                ->constrained('locations')
                ->cascadeOnUpdate()->nullOnDelete();

            $table->foreignId('permanent_ward_id')->nullable()
                ->constrained('locations')
                ->cascadeOnUpdate()->nullOnDelete();

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('applications', function (Blueprint $table) {

            $table->dropForeign(['permanent_location_type_id']);
            $table->dropForeign(['permanent_division_id']);
            $table->dropForeign(['permanent_district_id']);
            $table->dropForeign(['permanent_district_pourashava_id']);
            $table->dropForeign(['permanent_city_corp_id']);
            $table->dropForeign(['permanent_thana_id']);
            $table->dropForeign(['permanent_upazila_id']);
            $table->dropForeign(['permanent_union_id']);
            $table->dropForeign(['permanent_pourashava_id']);
            $table->dropForeign(['permanent_ward_id']);


            $table->dropColumn(
                'current_location_type_id', 'current_division_id', 'current_district_id',
                'current_district_pourashava_id', 'current_city_corp_id', 'current_thana_id', 'current_upazila_id',
                'current_union_id', 'current_pourashava_id', 'current_ward_id',
                'permanent_location_type_id', 'permanent_division_id', 'permanent_district_id',
                'permanent_district_pourashava_id', 'permanent_city_corp_id', 'permanent_thana_id', 'permanent_upazila_id',
                'permanent_union_id', 'permanent_pourashava_id', 'permanent_ward_id',
            );
        });
    }
};
