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
            $table->integer(('ministry_code'))->nullable()->after('payment_cycle');
            $table->integer(('scheme_code'))->nullable()->after('ministry_code');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('allowance_programs', function (Blueprint $table) {
            $table->dropColumn(['ministry_code','scheme_code']);
        });
    }
};
