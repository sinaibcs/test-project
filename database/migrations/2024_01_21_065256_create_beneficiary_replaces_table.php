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
        Schema::create('beneficiary_replaces', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('beneficiary_id');
            $table->foreign('beneficiary_id')->references('id')->on('locations')->onDelete('cascade');
            $table->unsignedBigInteger('replace_with_ben_id');
            $table->foreign('replace_with_ben_id')->references('id')->on('locations')->onDelete('cascade');
            $table->unsignedBigInteger('cause_id');
            $table->foreign('cause_id')->references('id')->on('lookups')->onDelete('cascade');
            $table->string('cause_detail')->nullable();
            $table->dateTime('cause_date');
            $table->string('cause_proof_doc')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('beneficiary_replaces');
    }
};
