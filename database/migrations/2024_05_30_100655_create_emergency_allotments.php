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
        Schema::create('emergency_allotments', function (Blueprint $table) {
            $table->id();
            $table->string('emergency_payment_name');
            $table->string('payment_cycle');
            $table->double('amount_per_person', 8, 2)->default(0.00);
            $table->integer('no_of_existing_benificiariy')->default(0);
            $table->integer('no_of_new_benificiariy')->default(0);
            $table->foreignId('division_id')->constrained('locations')->cascadeOnDelete()->cascadeOnUpdate();
            $table->foreignId('district_id')->constrained('locations')->cascadeOnDelete()->cascadeOnUpdate();
            $table->foreignId('location_type')->nullable()->constrained('lookups')->cascadeOnDelete()->cascadeOnUpdate();
            $table->foreignId('city_corp_id')->nullable()->constrained('locations')->cascadeOnDelete()->cascadeOnUpdate();
            $table->foreignId('district_pourashava_id')->nullable()->constrained('locations')->cascadeOnDelete()->cascadeOnUpdate();
            $table->foreignId('upazila_id')->nullable()->constrained('locations')->cascadeOnDelete()->cascadeOnUpdate();
            $table->foreignId('pourashava_id')->nullable()->constrained('locations')->cascadeOnDelete()->cascadeOnUpdate();
            $table->foreignId('thana_id')->nullable()->constrained('locations')->cascadeOnDelete()->cascadeOnUpdate();
            $table->foreignId('union_id')->nullable()->constrained('locations')->cascadeOnDelete()->cascadeOnUpdate();
            $table->foreignId('ward_id')->nullable()->constrained('locations')->cascadeOnDelete()->cascadeOnUpdate();
            $table->integer('location_id')->nullable();
            $table->foreignId('financial_year_id')->nullable()->constrained("financial_years")->cascadeOnUpdate()->cascadeOnDelete();
            $table->date('starting_period');
            $table->date('closing_period');
            $table->foreignId('created_by_id')->nullable()->constrained('users')->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('updated_by_id')->nullable()->constrained('users')->cascadeOnUpdate()->cascadeOnDelete();
            $table->tinyInteger('status')->default(0);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('emergency_allotments');
    }
};
