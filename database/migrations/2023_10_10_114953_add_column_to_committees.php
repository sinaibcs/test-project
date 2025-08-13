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
        Schema::table('committees', function (Blueprint $table) {
            $table->dropColumn('division_id');
            $table->dropColumn('district_id');
            $table->string('details',120)->nullable()->change();
            $table->unsignedBigInteger('committee_type')->index()->nullable();
            $table->foreign('committee_type')->references('id')->on('lookups')->onDelete('cascade');

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('committees', function (Blueprint $table) {
            //
        });
    }
};
