<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('beneficiaries', function (Blueprint $table) {
            // Create a temporary column to store the int values
            $table->integer('temp_religion')->nullable();
        });

        // Copy data from the varchar column to the temporary integer column
        DB::table('beneficiaries')->update([
            'temp_religion' => DB::raw('CAST(religion AS SIGNED)')
        ]);

        Schema::table('beneficiaries', function (Blueprint $table) {
            // Drop the old column
            $table->dropColumn('religion');

            // Rename the temporary column to the original name
            $table->renameColumn('temp_religion', 'religion');
        });
    }

    public function down()
    {
        Schema::table('beneficiaries', function (Blueprint $table) {
            // Create a temporary column to store the varchar values
            $table->string('temp_religion')->nullable();
        });

        // Copy data back from the int column to the temporary varchar column
        DB::table('beneficiaries')->update([
            'temp_religion' => DB::raw('CAST(religion AS CHAR)')
        ]);

        Schema::table('beneficiaries', function (Blueprint $table) {
            // Drop the int column
            $table->dropColumn('religion');

            // Rename the temporary column back to the original name
            $table->renameColumn('temp_religion', 'religion');
        });
    }
};
