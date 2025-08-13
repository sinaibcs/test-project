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
        Schema::table('training_programs', function (Blueprint $table) {
            $table->json('question_paper')->nullable()->change();
            $table->json('trainer_ratings_paper')->nullable()->after('trainer_ratings_link');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('training_programs', function (Blueprint $table) {
            $table->string('question_paper')->nullable()->change();
            $table->dropColumn('trainer_ratings_paper');
        });
    }
};
