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
        Schema::create('committees', function (Blueprint $table) {
            $table->id();
            $table->string('code',6);
            $table->string('name',50);
            $table->string('details',120);
            $table->bigInteger('program_id');
            $table->bigInteger('division_id');
            $table->bigInteger('district_id');
            // $table->bigInteger('office_id');
            $table->bigInteger('location_id')->nullable();
            $table->integer("version")->default(1);
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('committees');
    }
};
