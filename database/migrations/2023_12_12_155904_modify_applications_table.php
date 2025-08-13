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
        //
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
        Schema::table('applications', function (Blueprint $table) {
            $table->string('permanent_mobile', 20)->nullable()->change();
            $table->string('email', 50)->nullable()->change();
            $table->string('identification_mark', 20)->nullable()->change();
            $table->string('bank_name', 50)->nullable();
            $table->string('branch_name', 50)->nullable();
            
        });
    }
};
