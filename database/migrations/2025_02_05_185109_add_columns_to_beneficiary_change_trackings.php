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
        Schema::table('beneficiary_change_trackings', function (Blueprint $table) {
            $table->tinyInteger('status')->unsigned()->nullable();

            $table->longText('created_by')->nullable();
            $table->longText('verified_by')->nullable();
            $table->longText('approved_by')->nullable();

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('beneficiary_change_trackings', function (Blueprint $table) {
            $table->dropColumn(['status', 'created_by', 'verified_by', 'approved_by']);
        });
    }
};
