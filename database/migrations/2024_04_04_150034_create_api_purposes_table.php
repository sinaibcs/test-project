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
        Schema::create('api_purposes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('api_module_id')->constrained()->cascadeOnUpdate()->cascadeOnDelete();
            $table->string('api_unique_id');
            $table->string('purpose');
            $table->string('table_name')->nullable();
            $table->string('url')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('api_purposes');
    }
};
