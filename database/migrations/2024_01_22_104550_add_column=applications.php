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
            $table->string('bank_name')->nullable();
            $table->string('branch_name')->nullable();
            
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
         Schema::table('applications', function (Blueprint $table) {
            // Drop the 'account_type' column in the rollback
            $table->dropColumn('bank_name');
            $table->dropColumn('branch_name');
        });
    }
};
