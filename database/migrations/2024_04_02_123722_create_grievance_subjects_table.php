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
        Schema::create('grievance_subjects', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('grievance_type_id')->unsigned()->index();
            $table->string('title_en');
            $table->string('title_bn');
            $table->enum('status', array('1', '0'))->default('0')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('grievance_subjects');
    }
};