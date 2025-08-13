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
        Schema::create('devices', function (Blueprint $table) {
            $table->id();
                $table->unsignedBigInteger('user_id'); // Foreign key column
                $table->foreign('user_id')
                    ->references('user_id') // Column being referenced in the 'users' table
                    ->on('users')
                    ->onDelete('cascade');
         $table->string('name',50);
            $table->string('device_name',30)->nullable();
            $table->string('device_id',50)->nullable();
            $table->ipAddress()->nullable();
            $table->string('device_details',50)->nullable();
            $table->bigInteger('createdBy')->unsigned()->index();
            $table->foreign('createdBy')->references('id')->on('users')->onDelete('cascade');
            $table->integer('device_type')->nullable();
            $table->integer('status')->default(1);// 1 -> active, 2 -> deactive
            $table->string('purpose_use',120)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('devices');
    }
};
