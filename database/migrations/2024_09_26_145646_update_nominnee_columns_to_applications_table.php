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
        Schema::table('applications', function (Blueprint $table) {
            // Modify columns as needed
            $table->string('nominee_en')->nullable()->change();
            $table->string('nominee_bn')->nullable()->change();
            $table->string('nominee_verification_number', 16)->nullable()->change();
            $table->string('nominee_address')->nullable()->change();
            $table->string('nominee_image')->nullable()->change();
            $table->string('nominee_signature')->nullable()->change();
            $table->string('nominee_relation_with_beneficiary')->nullable()->change();
            $table->string('nominee_nationality')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('applications', function (Blueprint $table) {
            $table->string('nominee_en')->nullable()->change();
            $table->string('nominee_bn')->nullable()->change();
            $table->string('nominee_verification_number', 16)->nullable()->change();
            $table->string('nominee_address')->nullable()->change();
            $table->string('nominee_image')->nullable()->change();
            $table->string('nominee_signature')->nullable()->change();
            $table->string('nominee_relation_with_beneficiary')->nullable()->change();
            $table->string('nominee_nationality')->nullable()->change();
        });
    }
};