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
        Schema::table('allotments', function (Blueprint $table) {
            $table->dropForeign('allotments_location_id_foreign');
            $table->dropColumn('location_id');
            $table->foreignId('division_id')->constrained("locations")->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('district_id')->constrained("locations")->cascadeOnUpdate()->cascadeOnDelete();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('allotments', function (Blueprint $table) {
            $table->dropColumn('division_id');
            $table->dropColumn('district_id');
            $table->dropSoftDeletes();
        });
    }
};
