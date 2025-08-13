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
        Schema::table('poverty_score_cut_offs', function (Blueprint $table) {
            // Value "0" Cut Off for "1" District Fixed Effect

            $table->integer('default')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('poverty_score_cut_offs', function (Blueprint $table) {
            //
        });
    }
};
