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
        Schema::create('allowance_programs', function (Blueprint $table) {
            $table->id();
            $table->string('name_en',50);
            $table->string('name_bn',50);
            $table->string('payment_cycle', 40);
            $table->tinyInteger('is_marital')->nullable()->default(0);
            $table->string('marital_status', 40)->nullable();
            $table->tinyInteger('is_active')->default(0);
            $table->tinyInteger('is_age_limit')->nullable()->default(0);
            $table->tinyInteger('is_disable_class')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('allowance_programs');
    }
};
