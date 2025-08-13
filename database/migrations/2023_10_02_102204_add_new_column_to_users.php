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
        Schema::table('users', function (Blueprint $table) {
            // remove division_id, district_id, thana_id
            $table->dropColumn('division_id');
            $table->dropColumn('district_id');
            $table->dropColumn('thana_id');
     $table->unsignedInteger('office_type')->nullable();
     $table->integer('is_default_password')->default(1);

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            //
        });
    }
};
