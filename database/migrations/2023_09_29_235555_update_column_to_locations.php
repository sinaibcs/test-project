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
        Schema::table('locations', function (Blueprint $table) {
            // parent_id forgien key update add constraint on delete cascade and soft delete cascade
            $table->dropForeign(['parent_id']);
            $table->foreign('parent_id')->references('id')->on('locations')->onDelete('cascade')->onUpdate('cascade')->onSoftDelete('cascade');


        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('locations', function (Blueprint $table) {
            //
        });
    }
};
