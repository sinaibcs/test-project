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
        Schema::table('budgets', function (Blueprint $table) {
            $table->integer('previous_year_value')->unsigned()->change();
            $table->renameColumn('previous_year_value', 'no_of_previous_year');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('budgets', function (Blueprint $table) {
            $table->double('previous_year_value')->change();
            $table->renameColumn('no_of_previous_year', 'previous_year_value');
        });
    }
};
