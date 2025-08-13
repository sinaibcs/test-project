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
        Schema::create('api_selects', function (Blueprint $table) {
            $table->foreignId('api_data_receive_id')->constrained()->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('api_list_id')->constrained()->cascadeOnUpdate()->cascadeOnDelete();
            $table->unsignedInteger('total_hit')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('api_selects');
    }
};
