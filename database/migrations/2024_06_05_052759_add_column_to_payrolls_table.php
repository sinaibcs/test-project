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
        Schema::table('payrolls', function (Blueprint $table) {
            $table->boolean('is_submitted')->default(false);
            $table->foreignId('submitted_by_id')->nullable()->constrained('users')->cascadeOnUpdate()->cascadeOnDelete();
            $table->dateTime('submitted_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payrolls', function (Blueprint $table) {
            $table->dropForeign('payrolls_submitted_by_id_foreign');
            $table->dropColumn('submitted_by_id');
            $table->dropColumn('submitted_at');
            $table->dropColumn('is_submitted');
        });
    }
};
