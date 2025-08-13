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
            $table->date('passing_date')->nullable()->after('status');
            $table->boolean('invitation_status')->default(0)->after('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('training_program_participants', function (Blueprint $table) {
            $table->dropColumn('passing_date', 'invitation_status');
        });
    }
};
