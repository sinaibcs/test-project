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
        Schema::create('grievances', function (Blueprint $table) {
            $table->id();
            $table->string('tracking_no', 50);
            // entry
            $table->tinyInteger('is_existing_beneficiary');
            $table->bigInteger('resolver_id')->unsigned()->index();
            $table->integer('forward_to')->nullable();
            $table->longText('remarks')->nullable();
            $table->bigInteger('beneficiary_id')->unsigned()->index();
            $table->date('date_of_birth');
            $table->tinyInteger('verification_type')->nullable(); 
            $table->string('verification_number', 16);
            // information
            $table->string('name');
            $table->bigInteger('gender_id')->unsigned()->index();
            $table->bigInteger('program_id')->unsigned()->index();
            $table->foreign('program_id')->references('id')->on('allowance_programs')->onDelete('cascade');
            $table->string('email')->nullable();
            $table->string('mobile')->nullable();
            // complaint details
            $table->bigInteger('grievance_type_id')->unsigned()->index();
            $table->bigInteger('grievance_subject_id')->unsigned()->index();
            $table->string('details');
            $table->string('documents')->nullable();
            // area
            $table->integer('division_id')->nullable();
            $table->integer('district_id')->nullable();
            $table->integer('location_type')->nullable();
            $table->integer('thana_id')->nullable();
            $table->integer('sub_location_type')->nullable();
            $table->integer('union_id')->nullable();
            $table->integer('pouro_id')->nullable();
            $table->integer('city_id')->nullable();
            $table->integer('city_thana_id')->nullable();
            $table->integer('district_pouro_id')->nullable();
            $table->integer('ward_id_city')->nullable();
            $table->integer('ward_id_union')->nullable();
            $table->integer('ward_id_pouro')->nullable();
            $table->integer('ward_id_dist')->nullable();
            $table->integer('post_code')->nullable();
            $table->longText('address')->nullable();
            // status
            $table->integer('status')->default(0);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('grievances');
    }
};
