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
        Schema::table('beneficiaries', function (Blueprint $table) {
            $table->foreignId('bank_id')->nullable()->constrained('banks')->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('mfs_id')->nullable()->constrained('mfs')->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('bank_branch_id')->nullable()->constrained('bank_branches')->cascadeOnUpdate()->cascadeOnDelete();
            $table->boolean('is_replaced')->default(false);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('beneficiaries', function (Blueprint $table) {
            //
        });
    }
};
