<?php

// Tabel master bahan baku

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
        Schema::create('materials', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            // Nama Bahan (misal: Tepung)
            $table->string('unit');
            // Satuan (gram, butir, ml)
            $table->decimal('price_per_unit', 10, 2);
            // Harga per unit kecil (Rp) -> Kunci HPP

            $table->string('base_unit')->default('gram');
            // Base unit (contoh: kg / liter / butir). Nilai aktual biasanya disinkronkan dari unit.
            $table->decimal('price_per_base_unit', 10, 2)->nullable();
            // Harga per base unit (Rp) -> Kunci HPP

            $table->integer('current_stock')->default(0); // Stok awal 0

            $table->integer('min_stock_level')->default(0);
            // Minimal stok untuk peringatan (notifikasi)
            $table->timestamps(); // created_at, updated_at
            $table->softDeletes(); // deleted_at
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('materials');
    }
};
