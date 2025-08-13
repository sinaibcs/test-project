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
        Schema::create('api_lists', function (Blueprint $table) {
            $table->id();
            $table->foreignId('api_purpose_id')->constrained()->cascadeOnUpdate()->cascadeOnDelete();
            $table->string('api_unique_id');
            $table->string('name');
            $table->json('selected_columns');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('api_lists');
    }
};
