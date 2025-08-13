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
        Schema::table('lookups', function (Blueprint $table) {
           $table->string('value_en', 255)->change();
           $table->string('value_bn', 255)->change();

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('lookups', function (Blueprint $table) {
            $table->string('value_en', 255)->change();
            $table->string('value_bn', 255)->change();

        });
    }
};