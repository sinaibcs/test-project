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
        Schema::create('beneficiary_shiftings', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('beneficiary_id');
            $table->foreign('beneficiary_id')->references('id')->on('locations')->onDelete('cascade');
            $table->unsignedBigInteger('from_program_id');
            $table->foreign('from_program_id')->references('id')->on('allowance_programs')->onDelete('cascade');
            $table->unsignedBigInteger('to_program_id');
            $table->foreign('to_program_id')->references('id')->on('allowance_programs')->onDelete('cascade');
            $table->string('shifting_cause')->nullable();
            $table->date('activation_date');
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('beneficiary_shiftings');
    }
};
