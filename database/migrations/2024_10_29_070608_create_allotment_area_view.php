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
        DB::statement(
            "CREATE VIEW allotment_areas_view as
            SELECT
                l.location_type,
                l.id AS locatoin_id,
                l.id AS ward_id,
                NULL AS union_id,
                NULL AS pourashava_id,
                NULL AS thana_id,
                p1.id AS district_pourashava_id,
                NULL AS upazila_id,
                NULL AS city_corp_id,
                p2.id AS district_id,
                p3.id AS division_id
            FROM
                locations l
            JOIN locations p1 ON
                l.parent_id = p1.id
            JOIN locations p2 ON
                p1.parent_id = p2.id
            JOIN locations p3 ON
                p2.parent_id = p3.id
            WHERE
                l.location_type = 1
                AND l.`type` = 'ward'
                AND l.deleted_at IS NULL
            UNION
            SELECT
                p1.location_type,
                l.id AS locatoin_id,
                NULL AS ward_id,
                l.id AS union_id,
                NULL AS pourashava_id,
                NULL AS thana_id,
                NULL AS district_pourashava_id,
                p1.id AS upazila_id,
                NULL AS city_corp_id,
                p2.id AS district_id,
                p3.id AS division_id
            FROM
                locations l
            JOIN locations p1 ON
                l.parent_id = p1.id
            JOIN locations p2 ON
                p1.parent_id = p2.id
            JOIN locations p3 ON
                p2.parent_id = p3.id
            WHERE
                p1.location_type = 2
                AND l.`type` = 'union'
                AND l.deleted_at IS NULL
            UNION
            SELECT
                p1.location_type,
                l.id AS locatoin_id,
                NULL AS ward_id,
                NULL AS union_id,
                l.id AS pourashava_id,
                NULL AS thana_id,
                NULL AS district_pourashava_id,
                p1.id AS upazila_id,
                NULL AS city_corp_id,
                p2.id AS district_id,
                p3.id AS division_id
            FROM
                locations l
            JOIN locations p1 ON
                l.parent_id = p1.id
            JOIN locations p2 ON
                p1.parent_id = p2.id
            JOIN locations p3 ON
                p2.parent_id = p3.id
            WHERE
                p1.location_type = 2
                AND l.`type` = 'pouro'
                AND l.deleted_at IS NULL
            UNION
            SELECT
                l.location_type,
                l.id AS locatoin_id,
                l.id AS ward_id,
                NULL AS union_id,
                NULL AS pourashava_id,
                p1.id AS thana_id,
                NULL AS district_pourashava_id,
                p1.id AS upazila_id,
                p2.id AS city_corp_id,
                p3.id AS district_id,
                p4.id AS division_id
            FROM
                locations l
            JOIN locations p1 ON
                l.parent_id = p1.id
            JOIN locations p2 ON
                p1.parent_id = p2.id
            JOIN locations p3 ON
                p2.parent_id = p3.id
            JOIN locations p4 ON
                p3.parent_id = p4.id
            WHERE
                l.location_type = 3
                AND l.`type` = 'ward'
                AND l.deleted_at IS NULL"
        );
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("DROP VIEW allotment_areas_view");
    }
};
