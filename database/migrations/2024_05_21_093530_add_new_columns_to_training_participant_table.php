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
        Schema::table('training_participants', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->foreignId('user_id')->nullable()->change()->constrained()->cascadeOnUpdate()->nullOnDelete();
            $table->string('full_name')->nullable()->after('user_id');
            $table->string('email')->nullable()->after('full_name');
            $table->boolean('is_by_poll')->default(0)->after('document');
            $table->boolean('status')->default(1)
                ->after('document')
                ->comment('0=inactive;1=active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {

        Schema::table('training_participants', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropColumn('is_by_poll', 'status', 'full_name', 'email');
        });

        Schema::table('training_participants', function (Blueprint $table) {
            $table->foreignId('user_id')->nullable(false)->change()->constrained()->cascadeOnUpdate()->cascadeOnDelete();
        });

    }
};
