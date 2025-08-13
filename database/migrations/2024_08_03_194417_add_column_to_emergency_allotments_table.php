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
            $table->string('name_en')->after('id');
            $table->string('name_bn')->after('name_en');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('emergency_allotments', function (Blueprint $table) {
            $table->string('name_en')->after('id');
            $table->string('name_bn')->after('name_en');
        });
    }
};
