<?php

// Tabel untuk menyimpan header pesanan

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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('customer_name');    // Nama Pemesan
            $table->date('order_date');         // Tanggal Pesan
            $table->string('status')->default('pending'); // Status: pending, processing, done
            $table->integer('total_price')->default(0); // Total Harga Jual (Omzet)
            $table->integer('total_hpp')->default(0);   // Total Modal (Costing) -> Dihitung Otomatis nanti

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
