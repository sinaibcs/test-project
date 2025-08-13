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
            $table->boolean('is_verified')->default(false);
            $table->foreignId('verified_by_id')->nullable()->constrained('users')->cascadeOnUpdate()->cascadeOnDelete();
            $table->dateTime('verified_at')->nullable();
            $table->string('verified_note')->nullable();
            $table->string('verified_document')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payrolls', function (Blueprint $table) {
            $table->dropForeign('payrolls_verified_by_id_foreign');
            $table->dropColumn('verified_by_id');
            $table->dropColumn('verified_at');
            $table->dropColumn('is_verified');
            $table->dropColumn('verified_note');
            $table->dropColumn('verified_document');
        });
    }
};
