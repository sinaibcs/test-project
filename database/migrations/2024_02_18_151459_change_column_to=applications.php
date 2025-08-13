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
 
                 $table->string('permanent_mobile')->nullable()->change();
                 $table->string('email')->nullable()->change();
                 $table->string('identification_mark')->nullable()->change();
    });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
