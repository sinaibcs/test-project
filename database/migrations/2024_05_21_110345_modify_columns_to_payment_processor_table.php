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
        Schema::table('payroll_payment_processors', function (Blueprint $table) {
            $table->dropForeign(['processor_type_id']);
            $table->dropColumn('processor_type_id');

            // Add new processor_type column
            $table->enum('processor_type', ['bank', 'mfs', 'agent_banking']);

            // Make bank_id nullable
            $table->dropForeign(['bank_id']);
            $table->unsignedBigInteger('bank_id')->nullable()->change();

            // Make bank_branch_name and bank_routing_number nullable
            $table->string('bank_branch_name', 80)->nullable()->change();
            $table->string('bank_routing_number', 80)->nullable()->change();

            // Add new columns
            $table->string('name_en')->nullable()->after('bank_routing_number');
            $table->string('name_bn')->nullable()->after('name_en');
            $table->integer('charge')->nullable();
            $table->boolean('status')->default(true)->after('focal_phone_no');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payroll_payment_processors', function (Blueprint $table) {
            $table->dropColumn('processor_type');
            $table->dropColumn('name_en');
            $table->dropColumn('name_bn');
            $table->dropColumn('charge');
            $table->dropColumn('status');

            // Add processor_type_id column back and re-establish foreign key
            $table->foreignId('processor_type_id')->constrained('lookups')->cascadeOnUpdate()->cascadeOnDelete();

            // Change bank_id to not nullable and re-establish foreign key
            $table->unsignedBigInteger('bank_id')->nullable(false)->change();
            $table->foreign('bank_id')->references('id')->on('lookups')->cascadeOnUpdate()->cascadeOnDelete();

            // Change bank_branch_name and bank_routing_number to not nullable
            $table->string('bank_branch_name')->nullable(false)->change();
            $table->string('bank_routing_number')->nullable(false)->change();
        });
    }
};
