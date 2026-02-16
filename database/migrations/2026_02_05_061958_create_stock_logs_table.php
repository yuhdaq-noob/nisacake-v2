<?php

// NOTE: TABEL STOCK_LOGS

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
        Schema::create('stock_logs', function (Blueprint $table) {
            $table->id();
            // Terhubung ke bahan baku mana?
            $table->foreignId('material_id')->constrained('materials')->onDelete('cascade');

            // Jenis pergerakan: 'in' (Masuk/Belanja), 'out' (Keluar/Produksi), 'adjustment' (Koreksi)
            $table->enum('type', ['in', 'out', 'adjustment']);

            // Jumlah yang bergerak
            $table->integer('amount');

            // Keterangan (misal: "Belanja di Pasar" atau "Order #123")
            $table->string('description')->nullable();

            // Kapan terjadinya (created_at otomatis ada di sini)
            $table->timestamps();
        });
    }
};
