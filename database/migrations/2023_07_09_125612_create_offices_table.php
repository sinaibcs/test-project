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
        Schema::create('offices', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('division_id')->unsigned()->index()->nullable();
            $table->bigInteger('district_id')->unsigned()->index()->nullable();
            $table->bigInteger('thana_id')->unsigned()->index()->nullable();
            $table->bigInteger('parent_id')->unsigned()->index()->nullable();
            $table->string('name_en',50);
            $table->string('name_bn',50);
            $table->integer('office_type');
            $table->string('office_address',100);
            $table->string('comment',120)->nullable();
            $table->boolean('status');
            $table->integer("version")->default(1);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('offices');
    }
};
