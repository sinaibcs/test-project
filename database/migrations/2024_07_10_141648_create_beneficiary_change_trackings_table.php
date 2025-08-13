<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('beneficiary_change_trackings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('beneficiary_id')->constrained('beneficiaries')->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('change_type_id')->constrained('beneficiary_change_types')->cascadeOnUpdate()->cascadeOnDelete();
            $table->longText('previous_value')->nullable();
            $table->longText('change_value')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('beneficiary_change_trackings');
    }
};
