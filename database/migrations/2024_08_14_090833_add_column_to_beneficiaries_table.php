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
        Schema::table('beneficiaries', function (Blueprint $table) {
            $table->boolean('is_verified')->default(false);
            $table->integer('last_ver_fin_year_id')->nullable();
            $table->dateTime('last_verified_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('beneficiaries', function (Blueprint $table) {
            $table->dropColumn('is_verified');
            $table->dropColumn('last_ver_fin_year_id');
            $table->dropColumn('last_verified_at');
        });
    }
};
