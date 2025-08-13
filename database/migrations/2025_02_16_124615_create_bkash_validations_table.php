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
        Schema::create('bkash_validations', function (Blueprint $table) {
            $table->id();
            $table->char('phone_number', 11);
            $table->tinyInteger('status')->nullable();
            $table->dateTime('response_at')->nullable();
            $table->index('phone_number');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bkash_validations');
    }
};
