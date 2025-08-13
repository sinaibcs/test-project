<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('division_id')->nullable();
            $table->unsignedInteger('district_id')->nullable();
            $table->unsignedInteger('thana_id')->nullable();
            $table->string('username',50)->unique();
            $table->string('full_name',50)->nullable();
            $table->string('email',30)->unique()->nullable();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('mobile',13)->unique()->nullable();
            // $table->unsignedInteger('role_id');
            $table->unsignedInteger('office_id')->nullable();
            $table->string('password')->nullable();
            $table->rememberToken();

            // 1 -> Super-Admin, 
            // 2 -> Office-Head
            // 3 -> Committee-President

            $table->integer('user_type')->nullable(); 

            $table->integer('status')->default(0); // 0 -> deactivated, 1 -> activated, 2 -> banned
            $table->timestamps();
            $table->timestamp('deleted_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('users');
    }
};
