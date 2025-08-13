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
        Schema::table('nid_service_api_request_logs', function (Blueprint $table) {
            $table->enum('type', ['LOGIN', 'FETCH', 'LOGOUT'])->default('FETCH')->after('id');
            $table->index('type');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('nid_service_api_request_logs', function (Blueprint $table) {
            $table->dropIndex('type');
            $table->dropColumn('type');
            $table->dropIndex('created_at');
        });
    }
};
