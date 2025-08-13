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
        Schema::table('bank_branches', function (Blueprint $table) {
            $table->string('contact_name_en',300)->nullable()->change();
            $table->string('contact_name_bn',300)->nullable()->change();
            $table->string('contact_designation',300)->nullable()->change();
            $table->string('contact_email',300)->nullable()->change();
            $table->string('contact_mobile_no',20)->nullable()->change();
            $table->string('contact_telephone_number',20)->nullable()->change();
            $table->string('contact_address_en',300)->nullable()->change();
            $table->string('contact_address_bn',300)->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bank_branches', function (Blueprint $table) {
            $table->string('contact_name_en',255)->change();
            $table->string('contact_name_bn',255)->change();
            $table->string('contact_designation',255)->change();
            $table->string('contact_email',255)->change();
            $table->string('contact_mobile_no',20)->change();
            $table->string('contact_telephone_number',20)->change();
            $table->string('contact_address_en',255)->change();
            $table->string('contact_address_bn',255)->change();
        });
    }
};
