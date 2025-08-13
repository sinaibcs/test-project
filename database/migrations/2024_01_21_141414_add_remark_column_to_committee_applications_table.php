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
        Schema::table('committee_applications', function (Blueprint $table) {
            $table->string('remark', 1024)->nullable()
                ->after('status')
            ;
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('committee_applications', function (Blueprint $table) {
            $table->dropColumn('remark');
        });
    }
};
