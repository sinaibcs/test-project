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
        Schema::table('office_has_wards', function (Blueprint $table) {
            $table->foreignId('union_id')->nullable()->constrained('locations')->cascadeOnUpdate()->nullOnDelete();
            $table->foreignId('pouro_id')->nullable()->constrained('locations')->cascadeOnUpdate()->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('office_has_wards', function (Blueprint $table) {
            $table->dropForeign(['union_id']);
            $table->dropForeign(['pouro_id']);

            $table->dropColumn('union_id', 'pouro_id');
        });
    }
};
