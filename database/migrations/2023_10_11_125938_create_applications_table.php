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
        Schema::create('applications', function (Blueprint $table) {
            $table->id();
            $table->string('application_id', 50);
            $table->bigInteger('forward_committee_id')->unsigned()->index()->nullable();
            $table->foreign('forward_committee_id')->references('id')->on('committees')->onDelete('cascade');
            $table->string('remark',120)->nullable();
            $table->bigInteger('program_id')->unsigned()->index();
            $table->foreign('program_id')->references('id')->on('allowance_programs')->onDelete('cascade');
            $table->enum('verification_type', [1,2]); // 1=nid, 2=birth
            $table->string('verification_number', 16);
            $table->integer('age');
            $table->date('date_of_birth');
            $table->string('name_en');
            $table->string('name_bn');
            $table->string('mother_name_en');
            $table->string('mother_name_bn');
            $table->string('father_name_en');
            $table->string('father_name_bn');
            $table->string('spouse_name_en');
            $table->string('spouse_name_bn');
            $table->string('identification_mark');
            $table->string('image');
            $table->string('signature');
            $table->string('nationality');
            $table->bigInteger('gender_id')->unsigned()->index();
            $table->foreign('gender_id')->references('id')->on('lookups')->onDelete('cascade');
            $table->string('education_status');
            $table->string('profession');
            $table->string('religion');
            $table->bigInteger('current_location_id')->unsigned()->index();
            $table->foreign('current_location_id')->references('id')->on('locations')->onDelete('cascade');
            $table->string('current_post_code');
            $table->string('current_address');
            $table->string('mobile');
            $table->bigInteger('permanent_location_id')->unsigned()->index();
            $table->foreign('permanent_location_id')->references('id')->on('locations')->onDelete('cascade');
            $table->string('permanent_post_code');
            $table->string('permanent_address');
            $table->string('permanent_mobile');
            $table->string('nominee_en');
            $table->string('nominee_bn');
            $table->string('nominee_verification_number', 16);
            $table->string('nominee_address');
            $table->string('nominee_image');
            $table->string('nominee_signature');
            $table->string('nominee_relation_with_beneficiary');
            $table->string('nominee_nationality');
            $table->string('account_name');
            $table->string('account_number');
            $table->string('account_owner');
            $table->string('marital_status');
            $table->string('email');
            $table->integer('status')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('applications');
    }
};
