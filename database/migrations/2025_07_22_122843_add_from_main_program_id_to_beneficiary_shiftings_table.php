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
        Schema::table('beneficiary_shiftings', function (Blueprint $table) {
            $table->foreignId('from_main_program_id')
                ->nullable()
                ->after('id')
                ->constrained('allowance_programs')
                ->nullOnDelete();
            $table->foreignId('to_main_program_id')
                ->nullable()
                ->after('id')
                ->constrained('allowance_programs')
                ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('beneficiary_shiftings', function (Blueprint $table) {
            $table->dropForeign(['from_main_program_id']);
            $table->dropColumn('from_main_program_id');
            $table->dropForeign(['to_main_program_id']);
            $table->dropColumn('to_main_program_id');
        });
    }
};
