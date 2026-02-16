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
        Schema::create('material_price_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('material_id')->constrained('materials')->onDelete('cascade');
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->decimal('old_price_per_unit', 10, 2)->nullable();
            $table->decimal('new_price_per_unit', 10, 2)->nullable();
            $table->decimal('old_price_per_base_unit', 10, 2)->nullable();
            $table->decimal('new_price_per_base_unit', 10, 2)->nullable();
            $table->string('base_unit')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('material_price_logs');
    }
};
