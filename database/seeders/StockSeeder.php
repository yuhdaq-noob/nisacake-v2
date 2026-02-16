<?php

// NOTE: STOCK

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class StockSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Format: digunakan untuk menambahkan data dummy ke stock bahan baku
        $stocks = [
            1 => 25000, // Tepung Terigu (25 kg / 1 sak)
            2 => 300,   // Telur Ayam (300 butir / ~10 tray)
            3 => 1000,  // SP (1 kg)
            4 => 10000, // Minyak Goreng (10 liter/kg)
            5 => 20000, // Gula Pasir (20 kg)
            6 => 500,   // Pewarna Pandan (500 gram)
            7 => 2000,  // Coklat Bubuk (2 kg)
            8 => 5000,  // Coklat Batang (5 kg)
            9 => 5000,  // Butter Cream (5 kg)
            10 => 15000, // Mentega/Margarin (15 kg / 1 karton kecil)
            11 => 2000,  // Tepung Maizena (2 kg)
            12 => 2000,  // Susu Bubuk (2 kg)
            13 => 2000,  // Selai (2 kg)
            14 => 5000,  // Pisang (5 kg)
            15 => 2000,  // Santan Cair (2 liter/kg)
            16 => 1000,  // Garam (1 kg)
            17 => 500,   // Air Lemon (500 gram)
            18 => 3000,  // Parutan Kelapa (3 kg)
            19 => 15,  // mika ukuran 14
            20 => 15,  // mika ukuran 16
            21 => 15,  // mika ukuran 18
            22 => 15,   // mika ukuran 20
            23 => 15,  // mika ukuran 22
            24 => 15,  // mika ukuran 24
            25 => 15,  // kardus kue ukuran 16
            26 => 15,  // Kardus Kue ukuran 26
            27 => 200,  // plastik kue 
        ];

        // Eksekusi update data
        foreach ($stocks as $id => $quantity) {
            DB::table('materials')
                ->where('id', $id)
                ->update([
                    'current_stock' => $quantity,
                    'updated_at' => now(),
                ]);
        }

        $this->command->info('Berhasil mengisi stok untuk 18 bahan baku!');
    }
}
