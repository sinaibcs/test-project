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
        Schema::create('beneficiaries_allowance_values', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('beneficiary_id');
            $table->unsignedBigInteger('allow_addi_fields_id');
            $table->unsignedBigInteger('allow_addi_field_values_id')->nullable();
            $table->string('value')->nullable();

            $table->timestamps();

            $table->index('beneficiary_id', 'beneficiaries_allowance_values_beneficiary_id_index');
            $table->index('allow_addi_fields_id', 'beneficiaries_allowance_values_allow_addi_fields_id_index');
            $table->index('allow_addi_field_values_id', 'beneficiaries_allowance_values_allow_addi_field_values_id_index');

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('beneficiaries_allowance_values');
    }
};
