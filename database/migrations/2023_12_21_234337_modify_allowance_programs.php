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
            $table->string('payment_cycle', 40)->nullable()->change();
            $table->tinyInteger('pmt_status')->default(1);
            $table->tinyInteger('system_status')->default(1);
         
            
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
         Schema::table('allowance_programs', function (Blueprint $table) {
            $table->dropColumn('pmt_status');
            $table->dropColumn('system_status');
        });
    }
};
