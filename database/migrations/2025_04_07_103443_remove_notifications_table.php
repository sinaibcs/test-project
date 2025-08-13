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
        Schema::dropIfExists('notifications');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users');
            $table->string('title');
            $table->string('body')->nullable();
            $table->string('link')->nullable();
            $table->string('type')->nullable();
            $table->string('platform');
            $table->boolean('status')->default(0);
            $table->boolean('seen')->default(0);
            $table->dateTime('seen_at')->nullable();
            $table->bigInteger('create_time')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }
};
