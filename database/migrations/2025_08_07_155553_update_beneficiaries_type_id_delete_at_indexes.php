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
        Schema::table('beneficiaries', function (Blueprint $table) {
            $table->dropIndex('beneficiaries_type_id_index');

            $table->index(['type_id', 'deleted_at'], 'beneficiaries_type_id_deleted_at_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('beneficiaries', function (Blueprint $table) {
            $table->dropIndex('beneficiaries_type_id_deleted_at_index');

            $table->index('type_id', 'beneficiaries_type_id_index');
        });
    }
};
