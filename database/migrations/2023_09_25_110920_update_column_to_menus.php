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
        Schema::table('menus', function (Blueprint $table) {
            // change exiesting cloumn as a nullable
$table->bigInteger('page_link_id')->unsigned()->nullable()->change();
$table->integer("link_type")->nullable()->change()->after("label_name_bn"); // 1=internal, 2=external

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('menus', function (Blueprint $table) {
            //
        });
    }
};
