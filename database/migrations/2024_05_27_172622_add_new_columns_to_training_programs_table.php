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
            $table->string('question_link')->nullable()->after('description');
            $table->string('question_paper')->nullable()->after('question_link');
            $table->string('trainer_ratings_link')->nullable()->after('question_paper');
            $table->boolean('exam_status')->default(0)->after('status');
            $table->boolean('rating_status')->default(0)->after('exam_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('training_programs', function (Blueprint $table) {
            $table->dropColumn( 'question_link', 'question_paper', 'trainer_ratings_link', 'exam_status', 'rating_status');
        });
    }
};
