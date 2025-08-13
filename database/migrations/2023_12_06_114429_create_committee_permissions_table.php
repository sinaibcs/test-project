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
        Schema::create('committee_permissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('committee_type_id')->constrained('lookups',)
                ->cascadeOnUpdate()->cascadeOnDelete();
            $table->boolean('approve')->default(0);
            $table->boolean('forward')->default(0);
            $table->boolean('reject')->default(0);
            $table->boolean('waiting')->default(0);
            $table->foreignId('created_by')->nullable()->constrained('users')
                ->cascadeOnUpdate()->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('committee_permissions');
    }
};
