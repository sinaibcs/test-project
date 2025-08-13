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
        Schema::table('training_program_participants', function (Blueprint $table) {
            $table->string('rating', 3)->nullable()->after('trainer_rating_response');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('training_program_participants', function (Blueprint $table) {
            $table->dropColumn('rating');
        });
    }
};
