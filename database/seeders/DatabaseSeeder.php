<?php

// NOTE: INDUK SEEDER

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

// PENTING: Tambahkan ini untuk enkripsi password

class DatabaseSeeder extends Seeder
{
    public function run()
    {
        // Matikan foreign key agar bisa reset tabel
        Schema::disableForeignKeyConstraints();

        // Kosongkan semua tabel
        DB::table('order_items')->truncate();
        DB::table('orders')->truncate();
        DB::table('product_materials')->truncate();
        DB::table('products')->truncate();
        DB::table('materials')->truncate();
        DB::table('stock_logs')->truncate();
        DB::table('material_price_logs')->truncate();
        DB::table('personal_access_tokens')->truncate();
        DB::table('users')->truncate();

        // Aktifkan kembali foreign key
        Schema::enableForeignKeyConstraints();

        // Buat akun owner jika belum ada
        $this->call([
            OwnerSeeder::class,
        ]);

        // Isi data master dan dummy
        $this->call([
            OverheadSettingSeeder::class,
            MasterDataSeeder::class,
            OrderSeeder::class,
            StockSeeder::class, // Isi stok awal (hapus jika tidak perlu)
        ]);
    }
}
