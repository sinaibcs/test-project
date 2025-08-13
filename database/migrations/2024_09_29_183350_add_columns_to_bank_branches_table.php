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
            $table->string('swift_code',20);
            $table->tinyInteger('is_main_branch')->default(0);
            $table->string('contact_name_en',255);
            $table->string('contact_name_bn',255);
            $table->string('contact_designation',255);
            $table->string('contact_email',255);
            $table->string('contact_mobile_no',20);
            $table->string('contact_telephone_number',20);
            $table->string('contact_address_en',255);
            $table->string('contact_address_bn',255);

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bank_branches', function (Blueprint $table) {
            $table->dropColumn(['swift_code', 'is_main_branch', 'contact_name_en', 'contact_name_bn', 'contact_designation', 'contact_email', 'contact_mobile_no', 'contact_telephone_number', 'contact_address_en', 'contact_address_bn']);
        });
    }
};
