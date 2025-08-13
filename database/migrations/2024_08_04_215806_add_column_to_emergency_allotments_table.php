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
        Schema::table('emergency_allotments', function (Blueprint $table) {
            $table->integer('total_beneficiaries')->default(0)->after('no_of_new_benificiariy');
            $table->double('total_amount', 20, 2)->default(0)->after('total_beneficiaries');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('emergency_allotments', function (Blueprint $table) {
            $table->integer('total_beneficiaries')->default(0)->after('no_of_new_benificiariy');
            $table->double('total_amount', 20, 2)->default(0)->after('total_beneficiaries');
        });
    }
};
