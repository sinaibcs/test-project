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
        Schema::create('grievance_status_updates', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('grievance_id')->unsigned()->index();
            $table->bigInteger('resolver_id')->unsigned()->index()->nullable();
            $table->integer('forward_to')->nullable();
            $table->string('status')->nullable();
            $table->longText('remarks')->nullable();
            $table->string('solution')->nullable();
            $table->string('file')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('grievance_status_updates');
    }
};
