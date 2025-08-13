<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasColumn('beneficiaries', 'inactive_cause_id')) {
            Schema::table('beneficiaries', function (Blueprint $table) {
                $table->foreignId('inactive_cause_id')->nullable()->constrained('lookups')->cascadeOnUpdate()->cascadeOnDelete();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('beneficiaries', function (Blueprint $table) {
            $table->dropConstrainedForeignId('inactive_cause_id');
        });
    }
};
