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
        Schema::table('emergency_beneficiaries', function (Blueprint $table) {
            
            $table->string('name_en')->nullable()->change();
            $table->string('name_bn')->nullable()->change();
            $table->string('mother_name_en')->nullable()->change();
            $table->string('mother_name_bn')->nullable()->change();
            $table->string('father_name_en')->nullable()->change();
            $table->string('father_name_bn')->nullable()->change();
            $table->string('age')->nullable()->change();
            $table->string('nationality')->nullable()->change();
            $table->string('religion')->nullable()->change();
            $table->string('marital_status')->nullable()->change();
            $table->string('current_post_code')->nullable()->change();
            $table->string('current_address')->nullable()->change();
            $table->string('permanent_post_code')->nullable()->change();
            $table->string('permanent_address')->nullable()->change();
            $table->string('account_name')->nullable()->change();
            $table->string('account_number')->nullable()->change();
            $table->string('account_owner')->nullable()->change();
            $table->string('mobile')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('emergency_beneficiaries', function (Blueprint $table) {

            $table->string('name_en')->nullable()->change();
            $table->string('name_bn')->nullable()->change();
            $table->string('mother_name_en')->nullable()->change();
            $table->string('mother_name_bn')->nullable()->change();
            $table->string('father_name_en')->nullable()->change();
            $table->string('father_name_bn')->nullable()->change();
            $table->string('age')->nullable()->change();
            $table->string('nationality')->nullable()->change();
            $table->string('religion')->nullable()->change();
            $table->string('marital_status')->nullable()->change();
            $table->string('current_post_code')->nullable()->change();
            $table->string('current_address')->nullable()->change();
            $table->string('permanent_post_code')->nullable()->change();
            $table->string('permanent_address')->nullable()->change();
            $table->string('account_name')->nullable()->change();
            $table->string('account_number')->nullable()->change();
            $table->string('account_owner')->nullable()->change();
            $table->string('mobile')->nullable()->change();
        });
    }
};
