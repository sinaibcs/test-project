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
            $table->integer('temp_nationality')->nullable();
        });

        // Copy data from the varchar column to the temporary integer column
        DB::table('beneficiaries')->update([
            'temp_nationality' => DB::raw('CAST(nationality AS SIGNED)')
        ]);

        Schema::table('beneficiaries', function (Blueprint $table) {
            // Drop the old column
            $table->dropColumn('nationality');

            // Rename the temporary column to the original name
            $table->renameColumn('temp_nationality', 'nationality');
        });
    }

    public function down()
    {
        Schema::table('beneficiaries', function (Blueprint $table) {
            // Create a temporary column to store the varchar values
            $table->string('temp_nationality')->nullable();
        });

        // Copy data back from the int column to the temporary varchar column
        DB::table('beneficiaries')->update([
            'temp_nationality' => DB::raw('CAST(nationality AS CHAR)')
        ]);

        Schema::table('beneficiaries', function (Blueprint $table) {
            // Drop the int column
            $table->dropColumn('nationality');

            // Rename the temporary column back to the original name
            $table->renameColumn('temp_nationality', 'nationality');
        });
    }
};
