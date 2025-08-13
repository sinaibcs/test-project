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
        Schema::create('mfs_account_verifications', function (Blueprint $table) {
            $table->id();
            $table->morphs('verifiable', 'verifiable');
            $table->dateTime('mfs_last_verification_attempt_at')->nullable();
            $table->dateTime('mfs_last_verified_at')->nullable();
            $table->tinyInteger('mfs_last_verification_status')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mfs_account_verifications');
    }
};
