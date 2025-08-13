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
        Schema::create('emergency_beneficiaries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('allotment_id')->index()->nullable()->constrained('emergency_allotments')->cascadeOnDelete();
            $table->foreignId('program_id')->index()->nullable()->constrained('allowance_programs')->cascadeOnDelete();
            $table->string('application_id')->nullable();
            $table->string('beneficiary_id')->nullable();
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
            $table->date('date_of_birth')->nullable();
            $table->string('nationality');
            $table->foreignId('gender_id')->constrained('lookups')->cascadeOnUpdate()->cascadeOnDelete();
            $table->string('education_status')->nullable();
            $table->string('profession')->nullable();
            $table->string('religion');
            $table->string('marital_status');
            $table->string('email')->nullable();
            $table->enum('verification_type', [0, 1, 2]); //0 = Unverified 1 = nid 2= birth registration no
            $table->string('verification_number')->nullable();
            $table->string('image')->nullable();
            $table->string('signature')->nullable();
            $table->foreignId('current_division_id')->constrained('locations')->cascadeOnDelete()->cascadeOnUpdate();
            $table->foreignId('current_district_id')->constrained('locations')->cascadeOnDelete()->cascadeOnUpdate();
            $table->foreignId('current_location_type')->nullable()->constrained('lookups')->cascadeOnDelete()->cascadeOnUpdate();
            $table->foreignId('current_city_corp_id')->nullable()->constrained('locations')->cascadeOnDelete()->cascadeOnUpdate();
            $table->foreignId('current_district_pourashava_id')->nullable()->constrained('locations')->cascadeOnDelete()->cascadeOnUpdate();
            $table->foreignId('current_upazila_id')->nullable()->constrained('locations')->cascadeOnDelete()->cascadeOnUpdate();
            $table->foreignId('current_pourashava_id')->nullable()->constrained('locations')->cascadeOnDelete()->cascadeOnUpdate();
            $table->foreignId('current_thana_id')->nullable()->constrained('locations')->cascadeOnDelete()->cascadeOnUpdate();
            $table->foreignId('current_union_id')->nullable()->constrained('locations')->cascadeOnDelete()->cascadeOnUpdate();
            $table->foreignId('current_ward_id')->nullable()->constrained('locations')->cascadeOnDelete()->cascadeOnUpdate();
            $table->foreignId('current_location_id')->nullable()->constrained('locations')->cascadeOnDelete()->cascadeOnUpdate();
            $table->string('current_post_code');
            $table->string('current_address');
            $table->string('current_mobile')->nullable();
            $table->foreignId('permanent_division_id')->constrained('locations')->cascadeOnDelete()->cascadeOnUpdate();
            $table->foreignId('permanent_district_id')->constrained('locations')->cascadeOnDelete()->cascadeOnUpdate();
            $table->foreignId('permanent_location_type')->nullable()->constrained('lookups')->cascadeOnDelete()->cascadeOnUpdate();
            $table->foreignId('permanent_city_corp_id')->nullable()->constrained('locations')->cascadeOnDelete()->cascadeOnUpdate();
            $table->foreignId('permanent_district_pourashava_id')->nullable()->constrained('locations')->cascadeOnDelete()->cascadeOnUpdate();
            $table->foreignId('permanent_upazila_id')->nullable()->constrained('locations')->cascadeOnDelete()->cascadeOnUpdate();
            $table->foreignId('permanent_pourashava_id')->nullable()->constrained('locations')->cascadeOnDelete()->cascadeOnUpdate();
            $table->foreignId('permanent_thana_id')->nullable()->constrained('locations')->cascadeOnDelete()->cascadeOnUpdate();
            $table->foreignId('permanent_union_id')->nullable()->constrained('locations')->cascadeOnDelete()->cascadeOnUpdate();
            $table->foreignId('permanent_ward_id')->nullable()->constrained('locations')->cascadeOnDelete()->cascadeOnUpdate();
            $table->foreignId('permanent_location_id')->nullable()->constrained('locations')->cascadeOnDelete()->cascadeOnUpdate();
            $table->string('permanent_post_code');
            $table->string('permanent_address');
            $table->string('permanent_mobile')->nullable();
            $table->string('nominee_en')->nullable();
            $table->string('nominee_bn')->nullable();
            $table->string('nominee_verification_number')->nullable();
            $table->string('nominee_address')->nullable();
            $table->string('nominee_image')->nullable();
            $table->string('nominee_signature')->nullable();
            $table->string('nominee_relation_with_beneficiary')->nullable();
            $table->string('nominee_nationality')->nullable();
            $table->date('nominee_date_of_birth')->nullable();
            $table->string('account_name');
            $table->string('account_number');
            $table->string('account_owner');
            $table->unsignedTinyInteger('account_type')->nullable()->comment("1=Bank;2=Mobile");
            $table->string('bank_name')->nullable();
            $table->string('branch_name')->nullable();
            $table->double('monthly_allowance', 8, 2)->nullable();
            $table->tinyInteger('status')->default(0);
            $table->tinyInteger('isExisting')->default(0);
            $table->tinyInteger('isSelected')->default(0);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('emergency_beneficiaries');
    }
};
