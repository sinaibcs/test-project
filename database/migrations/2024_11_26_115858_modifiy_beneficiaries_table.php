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
//        Schema::table('beneficiaries', function (Blueprint $table) {
//        });
        DB::statement('ALTER TABLE beneficiaries MODIFY COLUMN age SMALLINT UNSIGNED DEFAULT NULL NULL;');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('beneficiaries', function (Blueprint $table) {
            $table->string('age')->change();
        });
    }
};
