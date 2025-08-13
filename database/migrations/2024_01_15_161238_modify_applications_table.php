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
            // Make spouse_name_en nullable
            $table->string('spouse_name_en')->nullable()->change();

            // Make spouse_name_bn nullable
            $table->string('spouse_name_bn')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        
    }
};
