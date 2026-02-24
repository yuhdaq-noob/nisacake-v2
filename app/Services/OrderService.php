<?php

namespace App\Services;

use App\Enums\OrderStatus;
use App\Enums\StockLogType;
use App\Exceptions\InsufficientStockException;
use App\Exceptions\MaterialNotFoundException;
use App\Models\Material;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\StockLog;
use App\Services\OverheadService;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class OrderService
{
    /**
     * Buat order baru dengan validasi dan pengurangan stok (pembelian langsung)
     * Status langsung menjadi COMPLETED karena pesanan dibuat dan dikonfirmasi segera
     *
     * @throws InsufficientStockException
     * @throws MaterialNotFoundException
     */
    public function createOrder(array $data): Order
    {
        $totalNeeds = $this->calculateTotalNeeds($data['items']);
        $shouldDeductStock = true;

        return $this->createOrderWithValidation($data, $totalNeeds, $shouldDeductStock, OrderStatus::COMPLETED);
    }

    /**
     * Buat pre-order tanpa pengurangan stok (pembelian terjadwal)
     *
     * @throws MaterialNotFoundException
     */
    public function createPreOrder(array $data): Order
    {
        // Pre-order tidak perlu validasi stok, hanya validasi bahwa produk ada
        $this->validateProductsExist($data['items']);

        $totalNeeds = $this->calculateTotalNeeds($data['items']);
        $shouldDeductStock = false;

        return $this->createOrderWithValidation($data, $totalNeeds, $shouldDeductStock, OrderStatus::PRE_ORDER);
    }

    /**
     * Metode internal untuk membuat order dengan opsi pengurangan stok
     * Mengikuti prinsip Single Responsibility: menangani logika pembuatan order dan item
     *
     * @throws InsufficientStockException
     * @throws MaterialNotFoundException
     */
    private function createOrderWithValidation(
        array $data,
        array $totalNeeds,
        bool $shouldDeductStock,
        OrderStatus $status = OrderStatus::COMPLETED
    ): Order {
        $materialIds = array_keys($totalNeeds);

        return DB::transaction(function () use ($data, $totalNeeds, $materialIds, $shouldDeductStock, $status) {
            $materials = collect();

            // Hanya validasi dan kunci material jika diperlukan pengurangan stok
            if ($shouldDeductStock && count($materialIds) > 0) {
                $materials = Material::whereIn('id', $materialIds)
                    ->lockForUpdate()
                    ->get()
                    ->keyBy('id');

                $this->validateStockAvailability($materials, $totalNeeds);
            }

            // Hitung data item order (harga, HPP)
            [$totalPrice, $totalHPP, $orderItemsData] = $this->calculateOrderItemsData($data, $materials);

            // Buat entri order
            $order = Order::create([
                'customer_name' => $data['customer_name'],
                'order_date' => now(),
                'status' => $status->value,
                'total_price' => $totalPrice,
                'total_hpp' => $totalHPP,
                'scheduled_at' => $data['scheduled_at'] ?? null,
            ]);

            // Buat entri item order
            foreach ($orderItemsData as $item) {
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $item['product']->id,
                    'quantity' => $item['quantity'],
                    'price_per_unit' => $item['price'],
                    'hpp_per_unit' => $item['hpp_per_unit'],
                ]);
            }

            // Kurangi stok hanya jika ini order langsung
            if ($shouldDeductStock) {
                $this->deductStockForOrder($data, $materials);
            }

            // Catat pembuatan order ke log bisnis
            Log::channel('business')->info('Order created', [
                'order_id' => $order->id,
                'customer_name' => $order->customer_name,
                'status' => $status->label(),
                'total_price' => $order->total_price,
                'total_hpp' => $order->total_hpp,
            ]);

            return $order;
        });
    }

    /**
     * Eksekusi pre-order: ubah status PRE_ORDER ke COMPLETED dengan pengurangan stok
     * Dipanggil ketika pengguna menekan "Bayar" pada pesanan terjadwal
     *
     * @throws InsufficientStockException
     * @throws MaterialNotFoundException
     */
    public function executePreOrder(Order $preOrder): Order
    {
        // Validasi bahwa order memang pre-order
        // Status di-cast ke enum OrderStatus, bandingkan dengan enum, bukan string
        if ($preOrder->status !== OrderStatus::PRE_ORDER) {
            throw new \InvalidArgumentException('Only pre-orders can be executed.');
        }

        // Bangun array item dari order items (pastikan relasi items sudah dimuat)
        if (!$preOrder->relationLoaded('items')) {
            $preOrder->load('items');
        }

        $items = $preOrder->items->map(fn($item) => [
            'product_id' => $item->product_id,
            'quantity' => $item->quantity,
        ])->toArray();

        // Hitung kebutuhan material untuk validasi stok
        $totalNeeds = $this->calculateTotalNeeds($items);
        $materialIds = array_keys($totalNeeds);

        return DB::transaction(function () use ($preOrder, $items, $totalNeeds, $materialIds) {
            $materials = collect();

            // Validasi dan kunci material untuk pengurangan stok
            if (count($materialIds) > 0) {
                $materials = Material::whereIn('id', $materialIds)
                    ->lockForUpdate()
                    ->get()
                    ->keyBy('id');

                $this->validateStockAvailability($materials, $totalNeeds);
            }

            // Perbarui status order dari PRE_ORDER ke COMPLETED
            $preOrder->status = OrderStatus::COMPLETED;
            $preOrder->order_date = now();
            $preOrder->save();

            // Kurangi stok sekarang setelah order dikonfirmasi
            $this->deductStockForOrder(['items' => $items], $materials);

            // Catat konversi pre-order ke completed di log bisnis
            Log::channel('business')->info('Pre-order executed and converted to completed', [
                'order_id' => $preOrder->id,
                'customer_name' => $preOrder->customer_name,
                'total_price' => $preOrder->total_price,
            ]);

            return $preOrder->fresh();
        });
    }

    /**
     * Hitung data item order (total dan informasi per-item)
     * Mengikuti Single Responsibility: hanya menangani perhitungan harga/HPP
     */
    private function calculateOrderItemsData(array $data, Collection $materials): array
    {
        $totalPrice = 0;
        $totalHPP = 0;
        $orderItemsData = [];

        foreach ($data['items'] as $item) {
            $product = Product::with('materials')->findOrFail($item['product_id']);

            $subtotal = $product->selling_price * $item['quantity'];
            $subhpp = $this->calculateRealTimeHPP($product, $item['quantity']);
            $hppPerUnit = $subhpp / $item['quantity'];

            $totalPrice += $subtotal;
            $totalHPP += $subhpp;

            $orderItemsData[] = [
                'product' => $product,
                'quantity' => $item['quantity'],
                'price' => $product->selling_price,
                'hpp_per_unit' => $hppPerUnit,
            ];
        }

        return [$totalPrice, $totalHPP, $orderItemsData];
    }

    /**
     * Kurangi stok pada material
     * Mengikuti Single Responsibility: hanya menangani logika pengurangan stok
     */
    private function deductStockForOrder(array $data, Collection $materials): void
    {
        foreach ($data['items'] as $item) {
            $product = Product::with('materials')->findOrFail($item['product_id']);

            if ($product->materials->count() > 0) {
                foreach ($product->materials as $material) {
                    $qtyNeeded = $material->pivot->quantity_needed * $item['quantity'];
                    $lockedMaterial = $materials->get($material->id) ?? $material;

                    // Kurangi jumlah stok
                    $lockedMaterial->decrement('current_stock', $qtyNeeded);

                    // Catat pengurangan stok
                    StockLog::create([
                        'material_id' => $material->id,
                        'type' => StockLogType::OUT->value,
                        'amount' => $qtyNeeded,
                        'description' => "Production: {$product->name} ({$item['quantity']} units)",
                    ]);
                }
            }
        }
    }

    /**
     * Validasi bahwa semua produk di items ada
     *
     * @throws MaterialNotFoundException
     */
    private function validateProductsExist(array $items): void
    {
        foreach ($items as $item) {
            if (!Product::where('id', $item['product_id'])->exists()) {
                throw new MaterialNotFoundException($item['product_id']);
            }
        }
    }

    /**
     * Hitung HPP real-time dari BOM dan harga material saat ini
     */
    private function calculateRealTimeHPP(Product $product, int $quantity): float
    {
        $materialHppPerUnit = 0.0;

        foreach ($product->materials as $material) {
            $quantityNeeded = (float) $material->pivot->quantity_needed;
            $currentPrice = (float) ($material->price_per_unit ?? $material->price_per_base_unit ?? 0);

            $materialHppPerUnit += $quantityNeeded * $currentPrice;
        }

        // Hitung overhead per unit
        // Jika produk memiliki overhead_cost_per_unit > 0, gunakan sebagai override per produk.
        // Jika tidak, gunakan konfigurasi global dari tabel overhead_settings.
        $overheadPerUnit = (float) ($product->overhead_cost_per_unit ?? 0);

        if ($overheadPerUnit <= 0) {
            $overheadPerUnit = OverheadService::calculateOverheadPerUnit();
        }

        $hppPerUnit = $materialHppPerUnit + $overheadPerUnit;

        return $hppPerUnit * $quantity;
    }

    /**
     * Hitung total kebutuhan material untuk semua item
     */
    private function calculateTotalNeeds(array $items): array
    {
        $totalNeeds = [];

        foreach ($items as $item) {
            $product = Product::with('materials')->find($item['product_id']);

            if ($product && $product->materials->count() > 0) {
                foreach ($product->materials as $material) {
                    $perUnit = $material->pivot->quantity_needed ?? 0;
                    $totalForThisItem = $perUnit * $item['quantity'];

                    if (isset($totalNeeds[$material->id])) {
                        $totalNeeds[$material->id] += $totalForThisItem;
                    } else {
                        $totalNeeds[$material->id] = $totalForThisItem;
                    }
                }
            }
        }

        return $totalNeeds;
    }

    /**
     * Validasi ketersediaan stok untuk semua material
     *
     * @throws InsufficientStockException
     * @throws MaterialNotFoundException
     */
    private function validateStockAvailability(Collection $materials, array $totalNeeds): void
    {
        foreach ($totalNeeds as $materialId => $qtyNeeded) {
            $material = $materials->get($materialId);

            if (! $material) {
                throw new MaterialNotFoundException($materialId);
            }

            if ($material->current_stock < $qtyNeeded) {
                throw new InsufficientStockException(
                    $material->name,
                    $material->current_stock,
                    $qtyNeeded
                );
            }
        }
    }
}