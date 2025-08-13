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
        Schema::create('api_data_receives', function (Blueprint $table) {
            $table->id();
            $table->string('organization_name');
            $table->string('organization_phone', 20);
            $table->string('organization_email');
            $table->string('responsible_person_email')->nullable();
            $table->string('responsible_person_nid', 17)->nullable();
            $table->string('username', 60);
            $table->string('api_key');
            $table->ipAddress('whitelist_ip')->nullable();
            $table->dateTime('start_date');
            $table->dateTime('end_date')->nullable();
            $table->unsignedInteger('total_hit')->default(0);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('api_data_receives');
    }
};
