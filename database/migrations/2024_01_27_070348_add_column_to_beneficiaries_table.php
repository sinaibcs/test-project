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
        Schema::table('beneficiaries', function (Blueprint $table) {
            $table->bigInteger('financial_year_id')->unsigned()->index()->nullable();
            $table->foreign('financial_year_id')->references('id')->on('financial_years')->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('current_location_type_id')->nullable()
                ->constrained('lookups')->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('permanent_location_type_id')->nullable()
                ->constrained('lookups')->cascadeOnUpdate()->cascadeOnDelete();
            $table->unsignedTinyInteger('account_type')->nullable()->comment("1=Bank;2=Mobile");
            $table->string('bank_name')->nullable();
            $table->string('branch_name')->nullable();
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
