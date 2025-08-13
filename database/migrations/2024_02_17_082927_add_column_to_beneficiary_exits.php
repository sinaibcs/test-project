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
        Schema::table('beneficiary_exits', function (Blueprint $table) {
            $table->enum('previous_status', [1, 2, 3])->default(1); // 1=Active, 2=Inactive, 3=Waiting
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('beneficiary_exits', function (Blueprint $table) {
            //
        });
    }
};
