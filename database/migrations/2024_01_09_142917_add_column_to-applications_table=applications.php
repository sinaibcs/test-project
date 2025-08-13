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
            $table->bigInteger('cut_off_id')->unsigned()->index()->nullable();
            $table->foreign('cut_off_id')->references('id')->on('poverty_score_cut_offs')->onDelete('set null');
        });
       
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
         $table->dropForeign(['cut_off_id']);

            $table->dropColumn('cut_off_id');
    }
};
