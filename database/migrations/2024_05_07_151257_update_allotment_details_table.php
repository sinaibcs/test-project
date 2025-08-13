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
        Schema::table('allotment_details', function (Blueprint $table) {
            $table->dropForeign('allotment_details_allotment_id_foreign');
            $table->renameColumn('allotment_id', 'budget_id');
            $table->foreign('budget_id')->references('id')->on('budgets')->onUpdate('cascade')->onDelete('cascade');
            $table->dropForeign('allotment_details_office_id_foreign');
            $table->dropColumn('office_id');
            $table->renameColumn('beneficiary_regular', 'regular_beneficiaries');
            $table->renameColumn('beneficiary_total', 'total_beneficiaries');
            $table->renameColumn('allocated_money', 'total_amount');
            $table->double('per_beneficiary_amount');
            $table->integer('additional_beneficiaries')->nullable();
            $table->foreignId('division_id')->constrained('locations')->cascadeOnDelete()->cascadeOnUpdate();
            $table->foreignId('district_id')->constrained('locations')->cascadeOnDelete()->cascadeOnUpdate();
            $table->foreignId('location_type')->nullable()->constrained('lookups')->cascadeOnDelete()->cascadeOnUpdate();
            $table->foreignId('city_corp_id')->nullable()->constrained('locations')->cascadeOnDelete()->cascadeOnUpdate();
            $table->foreignId('district_pourashava_id')->nullable()->constrained('locations')->cascadeOnDelete()->cascadeOnUpdate();
            $table->foreignId('upazila_id')->nullable()->constrained('locations')->cascadeOnDelete()->cascadeOnUpdate();
            $table->foreignId('sub_location_type')->nullable()->constrained('lookups')->cascadeOnDelete()->cascadeOnUpdate();
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
        Schema::table('allotment_details', function (Blueprint $table) {
            $table->dropColumn('additional_beneficiaries');
            $table->dropColumn('per_beneficiary_amount');
            $table->dropColumn('division_id');
            $table->dropColumn('district_id');
            $table->dropColumn('location_type');
            $table->dropColumn('city_corp_id');
            $table->dropColumn('district_pourashava_id');
            $table->dropColumn('sub_location_type');
            $table->dropColumn('pourashava_id');
            $table->dropColumn('thana_id');
            $table->dropColumn('union_id');
            $table->dropColumn('ward_id');
        });
    }
};
