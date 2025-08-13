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
        Schema::create('beneficiaries', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('program_id');
            $table->foreign('program_id')->references('id')->on('allowance_programs')->onDelete('cascade');
            $table->unsignedBigInteger('application_table_id')->nullable();
            $table->foreign('application_table_id', 'beneficiaries_app_id_foreign')->references('id')->on('applications')->onDelete('cascade');
            $table->string('application_id');
            $table->string('name_en');
            $table->string('name_bn');
            $table->string('mother_name_en');
            $table->string('mother_name_bn');
            $table->string('father_name_en');
            $table->string('father_name_bn');
            $table->string('spouse_name_en')->nullable();
            $table->string('spouse_name_bn')->nullable();
            $table->string('identification_mark')->nullable();
            $table->string('age');
            $table->date('date_of_birth');
            $table->date('nationality');
            $table->unsignedBigInteger('gender_id');
            $table->foreign('gender_id')->references('id')->on('lookups')->onDelete('cascade');
            $table->string('education_status');
            $table->string('profession');
            $table->string('religion');
            $table->string('marital_status');
            $table->string('email')->nullable();
            $table->enum('verification_type', [1, 2]); //1 = nid 2= birth registration no
            $table->string('verification_number');
            $table->string('image');
            $table->string('signature');

            $table->unsignedBigInteger('current_division_id');
            $table->foreign('current_division_id')->references('id')->on('locations')->onDelete('cascade');
            $table->unsignedBigInteger('current_district_id');
            $table->foreign('current_district_id')->references('id')->on('locations')->onDelete('cascade');
            $table->unsignedBigInteger('current_city_corp_id')->nullable();
            $table->foreign('current_city_corp_id')->references('id')->on('locations')->onDelete('cascade');
            $table->unsignedBigInteger('current_district_pourashava_id')->nullable();
            $table->foreign('current_district_pourashava_id', 'beneficiary_cur_dist_poura_id_fk')->references('id')->on('locations')->onDelete('cascade');
            $table->unsignedBigInteger('current_upazila_id')->nullable();
            $table->foreign('current_upazila_id')->references('id')->on('locations')->onDelete('cascade');
            $table->unsignedBigInteger('current_pourashava_id')->nullable();
            $table->foreign('current_pourashava_id')->references('id')->on('locations')->onDelete('cascade');
            $table->unsignedBigInteger('current_thana_id')->nullable();
            $table->foreign('current_thana_id')->references('id')->on('locations')->onDelete('cascade');
            $table->unsignedBigInteger('current_union_id')->nullable();
            $table->foreign('current_union_id')->references('id')->on('locations')->onDelete('cascade');
            $table->unsignedBigInteger('current_ward_id')->nullable();
            $table->foreign('current_ward_id')->references('id')->on('locations')->onDelete('cascade');
            $table->string('current_post_code');
            $table->string('current_address');
            $table->string('mobile')->nullable();

            $table->unsignedBigInteger('permanent_division_id');
            $table->foreign('permanent_division_id')->references('id')->on('locations')->onDelete('cascade');
            $table->unsignedBigInteger('permanent_district_id');
            $table->foreign('permanent_district_id')->references('id')->on('locations')->onDelete('cascade');
            $table->unsignedBigInteger('permanent_city_corp_id')->nullable();
            $table->foreign('permanent_city_corp_id')->references('id')->on('locations')->onDelete('cascade');
            $table->unsignedBigInteger('permanent_district_pourashava_id')->nullable();
            $table->foreign('permanent_district_pourashava_id', 'beneficiary_per_dist_poura_id_fk')->references('id')->on('locations')->onDelete('cascade');
            $table->unsignedBigInteger('permanent_upazila_id')->nullable();
            $table->foreign('permanent_upazila_id')->references('id')->on('locations')->onDelete('cascade');
            $table->unsignedBigInteger('permanent_pourashava_id')->nullable();
            $table->foreign('permanent_pourashava_id')->references('id')->on('locations')->onDelete('cascade');
            $table->unsignedBigInteger('permanent_thana_id')->nullable();
            $table->foreign('permanent_thana_id')->references('id')->on('locations')->onDelete('cascade');
            $table->unsignedBigInteger('permanent_union_id')->nullable();
            $table->foreign('permanent_union_id')->references('id')->on('locations')->onDelete('cascade');
            $table->unsignedBigInteger('permanent_ward_id')->nullable();
            $table->foreign('permanent_ward_id')->references('id')->on('locations')->onDelete('cascade');
            $table->string('permanent_post_code');
            $table->string('permanent_address');
            $table->string('permanent_mobile')->nullable();

            $table->string('nominee_en');
            $table->string('nominee_bn');
            $table->string('nominee_verification_number');
            $table->string('nominee_address');
            $table->string('nominee_image');
            $table->string('nominee_signature');
            $table->string('nominee_relation_with_beneficiary');
            $table->string('nominee_nationality');

            $table->string('account_name');
            $table->string('account_number');
            $table->string('account_owner');
            $table->enum('status', [1, 2, 3])->default(1); // 1=Active, 2=Inactive, 3=Waiting
            $table->integer('score');
            $table->unsignedBigInteger('forward_committee_id')->nullable();
            $table->string('remarks')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('beneficiaries');
    }
};
