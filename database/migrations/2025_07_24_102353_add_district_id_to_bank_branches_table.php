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
        Schema::table('bank_branches', function (Blueprint $table) {
            $table->unsignedBigInteger('district_id')
                ->nullable()
                ->after('is_main_branch')
                ->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bank_branches', function (Blueprint $table) {
            $table->dropIndex(['district_id']);
            $table->dropColumn('district_id');
        });
    }
};
