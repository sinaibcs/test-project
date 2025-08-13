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
        Schema::table('budgets', function (Blueprint $table) {
            $table->boolean('process_flag')->default(false);
            $table->boolean('allotment_create_flag')->default(false);
            $table->dropForeign('budgets_approved_by_id_foreign');
            $table->dropColumn('approved_by_id');
            $table->string('approved_by', '100')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('budgets', function (Blueprint $table) {
            $table->dropColumn('process_flag');
            $table->dropColumn('allotment_create_flag');
            $table->dropColumn('approved_by');
        });
    }
};
