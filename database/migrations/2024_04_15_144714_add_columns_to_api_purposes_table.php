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
        Schema::table('api_purposes', function (Blueprint $table) {
            $table->json('parameters')->nullable()->after('url');
            $table->string('method', 6)->after('parameters');
            $table->json('response')->nullable()->after('method');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('api_purposes', function (Blueprint $table) {
            $table->dropColumn('parameters', 'method', 'response');
        });
    }
};
