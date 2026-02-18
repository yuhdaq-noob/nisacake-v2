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
     * Create a new order with stock validation and deduction (immediate purchase)
     * Status goes directly to COMPLETED since order is placed and confirmed immediately
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
     * Create a pre-order without stock deduction (scheduled purchase)
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
     * Internal method to create order with optional stock deduction
     * Follows Single Responsibility: Handles order and item creation logic
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

            // Only validate and lock materials if stock deduction is needed
            if ($shouldDeductStock && count($materialIds) > 0) {
                $materials = Material::whereIn('id', $materialIds)
                    ->lockForUpdate()
                    ->get()
                    ->keyBy('id');

                $this->validateStockAvailability($materials, $totalNeeds);
            }

            // Calculate order items data (price, hpp)
            [$totalPrice, $totalHPP, $orderItemsData] = $this->calculateOrderItemsData($data, $materials);

            // Create order record
            $order = Order::create([
                'customer_name' => $data['customer_name'],
                'order_date' => now(),
                'status' => $status->value,
                'total_price' => $totalPrice,
                'total_hpp' => $totalHPP,
                'scheduled_at' => $data['scheduled_at'] ?? null,
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

            // Deduct stock only if this is an immediate order
            if ($shouldDeductStock) {
                $this->deductStockForOrder($data, $materials);
            }

            // Log the order creation
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
     * Execute pre-order: Convert PRE_ORDER to COMPLETED status with stock deduction
     * Called when user clicks "Bayar" on scheduled order
     *
     * @throws InsufficientStockException
     * @throws MaterialNotFoundException
     */
    public function executePreOrder(Order $preOrder): Order
    {
        // Validate that order is actually a pre-order
        // Status is cast to OrderStatus enum, so compare with enum not string
        if ($preOrder->status !== OrderStatus::PRE_ORDER) {
            throw new \InvalidArgumentException('Only pre-orders can be executed.');
        }

        // Build items array from order items (ensure items are loaded)
        if (!$preOrder->relationLoaded('items')) {
            $preOrder->load('items');
        }

        $items = $preOrder->items->map(fn($item) => [
            'product_id' => $item->product_id,
            'quantity' => $item->quantity,
        ])->toArray();

        // Calculate material needs for stock validation
        $totalNeeds = $this->calculateTotalNeeds($items);
        $materialIds = array_keys($totalNeeds);

        return DB::transaction(function () use ($preOrder, $items, $totalNeeds, $materialIds) {
            $materials = collect();

            // Validate and lock materials for stock deduction
            if (count($materialIds) > 0) {
                $materials = Material::whereIn('id', $materialIds)
                    ->lockForUpdate()
                    ->get()
                    ->keyBy('id');

                $this->validateStockAvailability($materials, $totalNeeds);
            }

            // Update order status from PRE_ORDER to COMPLETED
            $preOrder->status = OrderStatus::COMPLETED;
            $preOrder->order_date = now();
            $preOrder->save();

            // Deduct stock now that order is confirmed
            $this->deductStockForOrder(['items' => $items], $materials);

            // Log the conversion
            Log::channel('business')->info('Pre-order executed and converted to completed', [
                'order_id' => $preOrder->id,
                'customer_name' => $preOrder->customer_name,
                'total_price' => $preOrder->total_price,
            ]);

            return $preOrder->fresh();
        });
    }

    /**
     * Calculate order items data (totals and per-item info)
     * Follows Single Responsibility: Only handles price/hpp calculations
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
     * Deduct stock from materials
     * Follows Single Responsibility: Only handles stock deduction logic
     */
    private function deductStockForOrder(array $data, Collection $materials): void
    {
        foreach ($data['items'] as $item) {
            $product = Product::with('materials')->findOrFail($item['product_id']);

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
    }

    /**
     * Validate that all products in items exist
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