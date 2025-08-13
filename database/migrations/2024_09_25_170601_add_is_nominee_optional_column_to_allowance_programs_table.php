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
        Schema::table('allowance_programs', function (Blueprint $table) {
             $table->tinyInteger('is_nominee_optional')->default(0)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('allowance_programs', function (Blueprint $table) {
              $table->tinyInteger('is_nominee_optional')->nullable();
        });
    }
};