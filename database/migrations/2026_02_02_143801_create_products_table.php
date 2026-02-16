<?php

// FIXME: TABEL PRODUCTS

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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('name');             // Nama Kue (misal: Brownies Kukus)
            $table->integer('selling_price');   // Harga Jual ke Pelanggan
            $table->integer('production_cost')->default(0); // Set default value to 0
            $table->string('description', 1000)->nullable();
				    $table->decimal('overhead_cost_per_unit', 10, 2)->default(0);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
