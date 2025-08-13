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
        Schema::create('beneficiary_exits', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('beneficiary_id');
            $table->foreign('beneficiary_id')->references('id')->on('locations')->onDelete('cascade');
            $table->unsignedBigInteger('exit_reason_id');
            $table->foreign('exit_reason_id')->references('id')->on('lookups')->onDelete('cascade');
            $table->string('exit_reason_detail')->nullable();
            $table->dateTime('exit_date');
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('beneficiary_exits');
    }
};
