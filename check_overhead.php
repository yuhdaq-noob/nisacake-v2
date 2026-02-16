<?php
require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

// Check all products' overhead_cost_per_unit
$products = \App\Models\Product::all(['id', 'name', 'overhead_cost_per_unit']);
echo "Overhead per Produk:\n";
echo str_repeat("-", 60) . "\n";
foreach ($products as $p) {
    $overhead = $p->overhead_cost_per_unit;
    echo sprintf("%2d. %-30s => %s\n",
        $p->id,
        substr($p->name, 0, 28),
        ($overhead > 0 ? number_format($overhead, 2) : 'null/0')
    );
}

echo "\n" . str_repeat("-", 60) . "\n";
echo "Overhead Global dari OverheadService: " . number_format(\App\Services\OverheadService::calculateOverheadPerUnit(), 2) . "\n";
