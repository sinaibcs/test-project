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
        Schema::table('emergency_allotments', function (Blueprint $table) {
            
            $table->foreignId('sub_location_type')->after('location_type')->nullable()->constrained('lookups')->cascadeOnDelete()->cascadeOnUpdate();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('emergency_allotments', function (Blueprint $table) {
            $table->dropForeign(['sub_location_type']);
            $table->dropColumn('sub_location_type');
        });
    }
};
