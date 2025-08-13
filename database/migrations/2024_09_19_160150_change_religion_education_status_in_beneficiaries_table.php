<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('beneficiaries', function (Blueprint $table) {
            // Create a temporary column to store the int values
            $table->integer('temp_education_status')->nullable();
        });

        // Copy data from the varchar column to the temporary integer column
        DB::table('beneficiaries')->update([
            'temp_education_status' => DB::raw('CAST(education_status AS SIGNED)')
        ]);

        Schema::table('beneficiaries', function (Blueprint $table) {
            // Drop the old column
            $table->dropColumn('education_status');

            // Rename the temporary column to the original name
            $table->renameColumn('temp_education_status', 'education_status');
        });
    }

    public function down()
    {
        Schema::table('beneficiaries', function (Blueprint $table) {
            // Create a temporary column to store the varchar values
            $table->string('temp_education_status')->nullable();
        });

        // Copy data back from the int column to the temporary varchar column
        DB::table('beneficiaries')->update([
            'temp_education_status' => DB::raw('CAST(education_status AS CHAR)')
        ]);

        Schema::table('beneficiaries', function (Blueprint $table) {
            // Drop the int column
            $table->dropColumn('education_status');

            // Rename the temporary column back to the original name
            $table->renameColumn('temp_education_status', 'education_status');
        });
    }
};
