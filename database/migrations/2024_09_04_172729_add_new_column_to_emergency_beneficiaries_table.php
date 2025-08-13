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
        Schema::table('emergency_beneficiaries', function (Blueprint $table) {
            
            $table->foreignId('mfs_id')->nullable()->after('bank_id')->constrained('mfs')->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('bank_branch_id')->nullable()->after('mfs_id')->constrained('bank_branches')->cascadeOnUpdate()->cascadeOnDelete();
            $table->date('payment_start_date')->after('mfs_id')->nullable();
            $table->date('last_payment_date')->after('payment_start_date')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('emergency_beneficiaries', function (Blueprint $table) {
            //
        });
    }
};
