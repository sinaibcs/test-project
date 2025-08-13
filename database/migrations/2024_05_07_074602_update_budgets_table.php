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
            $table->boolean('is_approved')->default(false)->nullable(false);
            $table->enum('approval_status', ['Draft', 'Approved'])->default('Draft')->nullable(false);
            $table->foreignId('approved_by_id')->nullable()->constrained('users')->cascadeOnUpdate()->nullOnDelete();
            $table->datetime('approved_at')->nullable();
            $table->string('approved_document')->nullable();
            $table->string('approved_remarks')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('budgets', function (Blueprint $table) {
            $table->dropColumn('is_approved');
            $table->dropColumn('approval_status');
            $table->dropColumn('approved_by_id');
            $table->dropColumn('approved_at');
            $table->dropColumn('approved_document');
            $table->dropColumn('approved_remarks');
        });
    }
};
