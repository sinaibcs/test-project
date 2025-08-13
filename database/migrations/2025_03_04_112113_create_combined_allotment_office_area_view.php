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
        \DB::statement("
        CREATE VIEW combined_allotment_office_area_view AS SELECT
            location_type_id AS location_type,
            locatoin_id,
            NULL AS ward_id,
            NULL AS union_id,
            NULL AS pourashava_id,
            NULL AS thana_id,
            district_pourashava_id,
            upazila_id,
            city_corp_id,
            district_id,
            division_id,
            office_id
        FROM
            allotment_areas_1st_level_view
        WHERE
            district_pourashava_id IS NOT NULL
        UNION
        SELECT
            location_type,
            locatoin_id,
            ward_id,
            union_id,
            pourashava_id,
            thana_id,
            district_pourashava_id,
            upazila_id,
            city_corp_id,
            district_id,
            division_id,
            NULL AS office_id
        FROM
            allotment_areas_view
        WHERE
            district_pourashava_id IS NULL;
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        \DB::statement("DROP VIEW combined_allotment_office_area_view");
    }
};
