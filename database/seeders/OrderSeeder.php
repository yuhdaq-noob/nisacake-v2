<?php

namespace Database\Seeders;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Services\OverheadService;
use Illuminate\Database\Seeder;

class OrderSeeder extends Seeder
{

 // NOTE: DATA DUMMY PESANAN (ORDERS & ORDER ITEMS)
    /**
     * Jalankan seeder untuk membuat dummy orders.
     *
     * php artisan db:seed --class=OrderSeeder
     *
     *
     * Untuk reset orders dan items:
     * php artisan tinker
     * > Order::truncate(); UNTUK RESET TABEL orders
     * > OrderItem::truncate(); UNTUK RESET TABEL order_items
     * > exit
     */
    public function run(): void
    {
        // Pastikan ada produk sebelum membuat order
        // Ambil relasi materials agar seeder menghitung HPP sama seperti runtime
        $products = Product::with('materials')->get();

        if ($products->isEmpty()) {
            $this->command->info('Tidak ada produk! Jalankan MasterDataSeeder terlebih dahulu.');

            return;
        }

        $this->command->info('Membuat dummy orders...');

        // Buat 200 order dengan items di dalamnya
        Order::factory(200)
            ->completed()  // Gunakan orders yang sudah selesai (untuk laporan)
            ->create()
            ->each(function ($order) use ($products) {
                // Setiap order memiliki 1-2 item
                $itemCount = fake()->numberBetween(1, 2);
                $totalPrice = 0;
                $totalHpp = 0;

                // Ambil tanggal dari order_date dan tambahkan jam acak agar created_at tidak seragam
                $timestamp = \Carbon\Carbon::parse($order->order_date)->setTimeFromTimeString(fake()->time());

                for ($i = 0; $i < $itemCount; $i++) {
                    $product = $products->random();
                    $quantity = fake()->numberBetween(1, 2);

                    // Hitung total price
                    $totalPrice += $product->selling_price * $quantity;

                    // Hitung HPP per unit berdasarkan BOM (quantity_needed × harga bahan) + overhead
                    $hppPerUnit = 0.0;
                    if ($product->materials->count() > 0) {
                        foreach ($product->materials as $material) {
                            $quantityNeeded = (float) ($material->pivot->quantity_needed ?? 0);
                            // Ikuti preferensi yang sama dengan OrderService:
                            // utamakan price_per_unit (unit kecil), fallback ke price_per_base_unit jika perlu.
                            $currentPrice = (float) ($material->price_per_unit ?? $material->price_per_base_unit ?? 0);
                            $hppPerUnit += $quantityNeeded * $currentPrice;
                        }
                    }
                    // Ikuti logika yang sama dengan OrderService untuk overhead per unit:
                    // Jika produk memiliki overhead_cost_per_unit > 0, gunakan sebagai override per produk.
                    // Jika tidak, gunakan konfigurasi global dari tabel overhead_settings via OverheadService.
                    $overheadPerUnit = (float) ($product->overhead_cost_per_unit ?? 0);
                    if ($overheadPerUnit <= 0) {
                        $overheadPerUnit = OverheadService::calculateOverheadPerUnit();
                    }
                    $hppPerUnit += $overheadPerUnit;

                    // Total HPP untuk item ini (per unit × qty)
                    $totalHpp += $hppPerUnit * $quantity;

                    // Buat order item dengan informasi harga dan hpp per unit (mirip runtime)
                    $item = OrderItem::create([
                        'order_id' => $order->id,
                        'product_id' => $product->id,
                        'quantity' => $quantity,
                        'price_per_unit' => $product->selling_price,
                        'hpp_per_unit' => $hppPerUnit,
                    ]);
                    // Update timestamp item agar sesuai order (agar item tidak tercatat hari ini)
                    $item->created_at = $timestamp;
                    $item->updated_at = $timestamp;
                    $item->save();
                }

                // Update order dengan total price dan hpp
                $order->total_price = $totalPrice;
                $order->total_hpp = $totalHpp;
                $order->created_at = $timestamp;
                $order->updated_at = $timestamp;
                $order->save();
            });

        $this->command->info('Berhasil membuat 200 dummy orders dengan order items!');
    }
}
