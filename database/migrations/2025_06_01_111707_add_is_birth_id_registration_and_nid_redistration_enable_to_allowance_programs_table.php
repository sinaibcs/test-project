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
            $table->dropColumn('is_birth_id_registration_disabled');
            $table->tinyInteger('is_birth_id_registration_enabled')->default(1);
            $table->tinyInteger('is_nid_id_registration_enabled')->default(1);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('allowance_programs', function (Blueprint $table) {
            $table->dropColumn('is_birth_id_registration_enabled');
            $table->dropColumn('is_nid_id_registration_enabled');
            $table->tinyInteger('is_birth_id_registration_disabled')->default(0);
        });
    }
};
