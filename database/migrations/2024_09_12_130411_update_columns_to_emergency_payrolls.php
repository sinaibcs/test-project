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
        Schema::table('emergency_payrolls', function (Blueprint $table) {
            
            $table->double('total_charge', 20, 2)->default(0)->after('total_beneficiaries');
            $table->double('sub_total_amount', 20, 2)->default(0)->after('total_charge');
            $table->double('total_amount', 20, 2)->default(0)->after('sub_total_amount');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('emergency_payrolls', function (Blueprint $table) {});
    }
};
