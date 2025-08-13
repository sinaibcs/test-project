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
        Schema::create('otp_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id');
            $table->string('phone_number');
            $table->text('body');
            $table->enum('type', ['LOGIN', 'FORGOT_PASSWORD', 'RESET_PASSWORD', 'PAYROLL_CREATE', 'DEVICE_REGISTRATION', 'OTHER']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('otp_logs');
    }
};
