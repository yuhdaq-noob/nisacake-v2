<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Order;

$orders = Order::with(['items.product'])->orderBy('id')->take(3)->get();

$result = [];
foreach ($orders as $order) {
    $items = [];
    foreach ($order->items as $item) {
        $items[] = [
            'product_id' => $item->product_id,
            'product_name' => $item->product?->name,
            'quantity' => (int) $item->quantity,
            'price_per_unit' => (float) $item->price_per_unit,
            'hpp_per_unit' => (float) $item->hpp_per_unit,
            'total_hpp_for_item' => (float) $item->hpp_per_unit * (int) $item->quantity,
        ];
    }

    $result[] = [
        'order_id' => $order->id,
        'created_at' => $order->created_at?->toDateTimeString(),
        'total_price' => (float) $order->total_price,
        'total_hpp' => (float) $order->total_hpp,
        'profit' => (float) $order->total_price - (float) $order->total_hpp,
        'items' => $items,
    ];
}

echo json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . PHP_EOL;
