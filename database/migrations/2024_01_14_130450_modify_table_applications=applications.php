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
         Schema::table('applications', function (Blueprint $table) {
            $table->bigInteger('financial_year_id')->unsigned()->index()->nullable();
            $table->foreign('financial_year_id')->references('id')->on('financial_years')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
          $table->dropForeign(['financial_year_id']);

            $table->dropColumn('financial_year_id');
    }
};
