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
        Schema::table('emergency_beneficiaries', function (Blueprint $table) {
            // Drop the existing foreign key constraint
            // $table->dropForeign(['gender_id']);
            
            // Modify the `gender_id` column to allow NULL values
            $table->unsignedBigInteger('gender_id')->nullable()->change();
            
            // Re-add the foreign key constraint
            // $table->foreign('gender_id')
            //     ->after('nationality')
            //     ->references('id')
            //     ->on('lookups')
            //     ->onDelete('cascade'); // Use your desired action for onDelete
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('emergency_beneficiaries', function (Blueprint $table) {
            // Drop the modified foreign key constraint
            // $table->dropForeign(['gender_id']);
            
            // Modify the `gender_id` column to NOT NULL
            $table->unsignedBigInteger('gender_id')->nullable(false)->change();
            
            // Re-add the original foreign key constraint
            // $table->foreign('gender_id')
            //     ->after('nationality')
            //     ->references('id')
            //     ->on('lookups')
            //     ->onDelete('cascade'); // Restore the original onDelete action
        });
    }
};
