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
        Schema::create('beneficiary_location_shiftings', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('beneficiary_id');
            $table->foreign('beneficiary_id')->references('id')->on('locations')->onDelete('cascade');
            $table->unsignedBigInteger('from_division_id');
            $table->foreign('from_division_id')->references('id')->on('locations')->onDelete('cascade');
            $table->unsignedBigInteger('from_district_id');
            $table->foreign('from_district_id')->references('id')->on('locations')->onDelete('cascade');
            $table->unsignedBigInteger('from_city_corp_id')->nullable();
            $table->foreign('from_city_corp_id')->references('id')->on('locations')->onDelete('cascade');
            $table->unsignedBigInteger('from_district_pourashava_id')->nullable();
            $table->foreign('from_district_pourashava_id', 'from_beneficiary_dist_poura_id_fk')->references('id')->on('locations')->onDelete('cascade');
            $table->unsignedBigInteger('from_upazila_id')->nullable();
            $table->foreign('from_upazila_id')->references('id')->on('locations')->onDelete('cascade');
            $table->unsignedBigInteger('from_pourashava_id')->nullable();
            $table->foreign('from_pourashava_id')->references('id')->on('locations')->onDelete('cascade');
            $table->unsignedBigInteger('from_thana_id')->nullable();
            $table->foreign('from_thana_id')->references('id')->on('locations')->onDelete('cascade');
            $table->unsignedBigInteger('from_union_id')->nullable();
            $table->foreign('from_union_id')->references('id')->on('locations')->onDelete('cascade');
            $table->unsignedBigInteger('from_ward_id')->nullable();
            $table->foreign('from_ward_id')->references('id')->on('locations')->onDelete('cascade');
            $table->foreignId('from_location_type_id')->nullable()
                ->constrained('lookups')->cascadeOnUpdate()->cascadeOnDelete();
            $table->unsignedBigInteger('from_location_id')->nullable();
            $table->foreign('from_location_id')->references('id')->on('locations')->onDelete('cascade');
            $table->unsignedBigInteger('from_office_id')->nullable();
            $table->foreign('from_office_id')->references('id')->on('offices')->onDelete('cascade');
            $table->unsignedBigInteger('to_division_id');
            $table->foreign('to_division_id')->references('id')->on('locations')->onDelete('cascade');
            $table->unsignedBigInteger('to_district_id');
            $table->foreign('to_district_id')->references('id')->on('locations')->onDelete('cascade');
            $table->unsignedBigInteger('to_city_corp_id')->nullable();
            $table->foreign('to_city_corp_id')->references('id')->on('locations')->onDelete('cascade');
            $table->unsignedBigInteger('to_district_pourashava_id')->nullable();
            $table->foreign('to_district_pourashava_id', 'to_beneficiary_dist_poura_id_fk')->references('id')->on('locations')->onDelete('cascade');
            $table->unsignedBigInteger('to_upazila_id')->nullable();
            $table->foreign('to_upazila_id')->references('id')->on('locations')->onDelete('cascade');
            $table->unsignedBigInteger('to_pourashava_id')->nullable();
            $table->foreign('to_pourashava_id')->references('id')->on('locations')->onDelete('cascade');
            $table->unsignedBigInteger('to_thana_id')->nullable();
            $table->foreign('to_thana_id')->references('id')->on('locations')->onDelete('cascade');
            $table->unsignedBigInteger('to_union_id')->nullable();
            $table->foreign('to_union_id')->references('id')->on('locations')->onDelete('cascade');
            $table->unsignedBigInteger('to_ward_id')->nullable();
            $table->foreign('to_ward_id')->references('id')->on('locations')->onDelete('cascade');
            $table->foreignId('to_location_type_id')->nullable()
                ->constrained('lookups')->cascadeOnUpdate()->cascadeOnDelete();
            $table->unsignedBigInteger('to_location_id')->nullable();
            $table->foreign('to_location_id')->references('id')->on('locations')->onDelete('cascade');
            $table->unsignedBigInteger('to_office_id')->nullable();
            $table->foreign('to_office_id')->references('id')->on('offices')->onDelete('cascade');
            $table->string('shifting_cause')->nullable();
            $table->date('effective_date');
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('beneficiary_location_shiftings');
    }
};
