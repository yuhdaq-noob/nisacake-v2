<?php

// FIXME: PERHITUNGAN

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
     * Create a new order with stock validation and deduction
     *
     * @throws InsufficientStockException
     * @throws MaterialNotFoundException
     */
    public function createOrder(array $data): Order
    {
        // Calculate total needs for all items
        $totalNeeds = $this->calculateTotalNeeds($data['items']);
        $materialIds = array_keys($totalNeeds);

        // Create order in transaction
        return DB::transaction(function () use ($data, $totalNeeds, $materialIds) {
            $materials = collect();

            if (count($materialIds) > 0) {
                $materials = Material::whereIn('id', $materialIds)
                    ->lockForUpdate()
                    ->get()
                    ->keyBy('id');

                // Validate stock availability for all materials using locked rows
                $this->validateStockAvailability($materials, $totalNeeds);
            }

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

                // Deduct stock for each material
                if ($product->materials->count() > 0) {
                    foreach ($product->materials as $material) {
                        $qtyNeeded = $material->pivot->quantity_needed * $item['quantity'];
                        $lockedMaterial = $materials->get($material->id) ?? $material;

                        // Decrement stock
                        $lockedMaterial->decrement('current_stock', $qtyNeeded);

                        // Log stock deduction
                        StockLog::create([
                            'material_id' => $material->id,
                            'type' => StockLogType::OUT->value,
                            'amount' => $qtyNeeded,
                            'description' => "Production: {$product->name} ({$item['quantity']} units)",
                        ]);
                    }
                }
            }

            // Create order
            $order = Order::create([
                'customer_name' => $data['customer_name'],
                // FIXME: TIDAK DIPAKAI
                // order_date belum dipakai oleh UI (laporan memakai created_at->date).
                'order_date' => now(),
                'status' => OrderStatus::PENDING->value,
                'total_price' => $totalPrice,
                'total_hpp' => $totalHPP,
            ]);

            // Create order items
            foreach ($orderItemsData as $item) {
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $item['product']->id,
                    'quantity' => $item['quantity'],
                    'price_per_unit' => $item['price'],
                    'hpp_per_unit' => $item['hpp_per_unit'],
                ]);
            }

            Log::channel('business')->info('Order created', [
                'order_id' => $order->id,
                'customer_name' => $order->customer_name,
                'total_price' => $order->total_price,
                'total_hpp' => $order->total_hpp,
            ]);

            return $order;
        });
    }

    /**
     * Calculate real-time HPP from BOM and current material prices
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
     * Calculate total material needs for all items
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
     * Validate stock availability for all materials
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
