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
        Schema::table('payroll_payment_processors', function (Blueprint $table) {
            $table->foreignId('branch_id')->nullable()->after('bank_id')->constrained('bank_branches', 'id')->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('mfs_id')->nullable()->after('name_bn')->constrained('mfs', 'id')->cascadeOnUpdate()->cascadeOnDelete();
            $table->integer('created_by')->nullable()->after('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payroll_payment_processors', function (Blueprint $table) {
            $table->dropForeign(['branch_id']);
            $table->dropColumn('branch_id');
            $table->dropForeign(['mfs_id']);
            $table->dropColumn('mfs_id');
            $table->dropColumn('created_by');
        });
    }
};
