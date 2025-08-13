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
        Schema::table('beneficiaries', function (Blueprint $table) {
            $table->integer('program_id')->unsigned()->change();
            $table->integer('application_table_id')->unsigned()->change();
            $table->smallInteger('gender_id')->unsigned()->change();
            $table->integer('current_division_id')->unsigned()->change();
            $table->integer('current_district_id')->unsigned()->change();
            $table->integer('current_city_corp_id')->unsigned()->change();
            $table->integer('current_district_pourashava_id')->unsigned()->change();
            $table->integer('current_upazila_id')->unsigned()->change();
            $table->integer('current_pourashava_id')->unsigned()->change();
            $table->integer('current_thana_id')->unsigned()->change();
            $table->integer('current_union_id')->unsigned()->change();
            $table->integer('current_ward_id')->unsigned()->change();
            $table->integer('permanent_division_id')->unsigned()->change();
            $table->integer('permanent_district_id')->unsigned()->change();
            $table->integer('permanent_city_corp_id')->unsigned()->change();
            $table->integer('permanent_district_pourashava_id')->unsigned()->change();
            $table->integer('permanent_upazila_id')->unsigned()->change();
            $table->integer('permanent_pourashava_id')->unsigned()->change();
            $table->integer('permanent_thana_id')->unsigned()->change();
            $table->integer('permanent_union_id')->unsigned()->change();
            $table->integer('permanent_ward_id')->unsigned()->change();
            $table->integer('forward_committee_id')->unsigned()->change();
            $table->smallInteger('financial_year_id')->unsigned()->change();
            $table->smallInteger('current_location_type_id')->unsigned()->change();
            $table->smallInteger('permanent_location_type_id')->unsigned()->change();
            $table->smallInteger('score')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('beneficiaries', function (Blueprint $table) {
            //
        });
    }
};
