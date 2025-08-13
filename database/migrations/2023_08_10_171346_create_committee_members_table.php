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
        Schema::create('committee_members', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('committee_id')->unsigned();
            $table->foreign('committee_id')->references('id')->on('committees')->onDelete('cascade');
            $table->string('member_name',50);
            $table->string('designation',50);
            $table->string('email',50)->nullable();
            $table->string('address',50)->nullable();
            $table->bigInteger('phone');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('committee_members');
    }
};
