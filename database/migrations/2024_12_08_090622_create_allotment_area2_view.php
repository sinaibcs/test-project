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
        DB::statement("CREATE VIEW allotment_areas_1st_level_view as
            select
                o.id as office_id,
                l.location_type as location_type_id,
                'District Pouroshava' as location_type,
                l.id as locatoin_id,
                l.id as district_pourashava_id,
                null as upazila_id,
                null as city_corp_id,
                p1.id as district_id,
                p2.id as division_id
            from
                locations l
            join locations p1 on
                l.parent_id = p1.id
            join offices o on
                l.id = o.assign_location_id
            join locations p2 on
                p1.parent_id = p2.id
            where
                l.location_type = 1
                and l.`type` = 'city'
                and l.deleted_at is null
            union
            select
                o.id as office_id,
                l.location_type as location_type_id,
                'Upazila' as location_type,
                l.id as locatoin_id,
                null as district_pourashava_id,
                l.id as upazila_id,
                null as city_corp_id,
                p1.id as district_id,
                p2.id as division_id
            from
                locations l
            join offices o on
                l.id = o.assign_location_id
            join locations p1 on
                l.parent_id = p1.id
            join locations p2 on
                p1.parent_id = p2.id
            where
                l.location_type = 2
                and l.`type` = 'thana'
                and l.deleted_at is null
            union
            select
                o.id as office_id,
                l.location_type as location_type_id,
                'City Corporation' as location_type,
                l.id as locatoin_id,
                null as district_pourashava_id,
                null as upazila_id,
                l.id as city_corp_id,
                p1.id as district_id,
                p2.id as division_id
            from
                locations l
            join offices o on
                l.id = o.assign_location_id
            join locations p1 on
                l.parent_id = p1.id
            join locations p2 on
                p1.parent_id = p2.id
            where
                l.location_type = 3
                and l.`type` = 'city'
                and l.deleted_at is null"
        );
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("DROP VIEW allotment_areas_1st_level_view");
    }
};
