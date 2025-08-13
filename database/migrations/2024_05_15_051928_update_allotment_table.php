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
        Schema::table('allotments', function (Blueprint $table) {
            $table->foreignId('budget_id')->constrained('budgets')->cascadeOnUpdate()->cascadeOnDelete();
            $table->integer('regular_beneficiaries');
            $table->integer('additional_beneficiaries')->default(0);
            $table->integer('total_beneficiaries');
            $table->double('per_beneficiary_amount');
            $table->double('total_amount');
            $table->foreignId('location_type')->nullable()->constrained('lookups')->cascadeOnDelete()->cascadeOnUpdate();
            $table->foreignId('city_corp_id')->nullable()->constrained('locations')->cascadeOnDelete()->cascadeOnUpdate();
            $table->foreignId('district_pourashava_id')->nullable()->constrained('locations')->cascadeOnDelete()->cascadeOnUpdate();
            $table->foreignId('upazila_id')->nullable()->constrained('locations')->cascadeOnDelete()->cascadeOnUpdate();
            $table->foreignId('sub_location_type')->nullable()->constrained('lookups')->cascadeOnDelete()->cascadeOnUpdate();
            $table->foreignId('pourashava_id')->nullable()->constrained('locations')->cascadeOnDelete()->cascadeOnUpdate();
            $table->foreignId('thana_id')->nullable()->constrained('locations')->cascadeOnDelete()->cascadeOnUpdate();
            $table->foreignId('union_id')->nullable()->constrained('locations')->cascadeOnDelete()->cascadeOnUpdate();
            $table->foreignId('ward_id')->nullable()->constrained('locations')->cascadeOnDelete()->cascadeOnUpdate();
            $table->foreignId('location_id')->nullable()->constrained('locations')->cascadeOnDelete()->cascadeOnUpdate();
        });
        Schema::dropIfExists('allotment_details');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('allotments', function (Blueprint $table) {
            //
        });
    }
};
