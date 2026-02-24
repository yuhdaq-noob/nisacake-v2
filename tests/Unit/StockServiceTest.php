<?php

namespace Tests\Unit;

use App\Enums\StockLogType;
use App\Exceptions\InsufficientStockException;
use App\Models\Material;
use App\Models\StockLog;
use App\Services\StockService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StockServiceTest extends TestCase
{
    use RefreshDatabase;

    protected StockService $stockService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->stockService = new StockService();
    }

    /**
     * Test menambah stok berhasil
     */
    public function test_add_stock_successfully(): void
    {
        // Create material with initial stock
        $material = Material::create([
            'name' => 'Tepung Terigu',
            'unit' => 'gram',
            'base_unit' => 'kg',
            'price_per_unit' => 12.0,
            'price_per_base_unit' => 12000.0,
            'current_stock' => 1000,
            'min_stock_level' => 500,
        ]);

        $result = $this->stockService->addStock([
            'material_id' => $material->id,
            'amount' => 500,
            'description' => 'Pembelian dari supplier',
        ]);

        // Assert stock increased
        $this->assertEquals(1500, $result->current_stock);

        // Assert stock log created
        $this->assertDatabaseHas('stock_logs', [
            'material_id' => $material->id,
            'type' => StockLogType::IN->value,
            'amount' => 500,
            'description' => 'Pembelian dari supplier',
        ]);
    }

    /**
     * Test menambah stok dengan deskripsi kosong
     */
    public function test_add_stock_with_empty_description(): void
    {
        $material = Material::create([
            'name' => 'Telur Ayam',
            'unit' => 'butir',
            'base_unit' => 'Pack',
            'price_per_unit' => 1800.0,
            'price_per_base_unit' => 180000.0,
            'current_stock' => 10,
            'min_stock_level' => 5,
        ]);

        $result = $this->stockService->addStock([
            'material_id' => $material->id,
            'amount' => 20,
            'description' => '',
        ]);

        $this->assertEquals(30, $result->current_stock);
    }

    /**
     * Test mengurangi stok berhasil
     */
    public function test_reduce_stock_successfully(): void
    {
        $material = Material::create([
            'name' => 'Gula Pasir',
            'unit' => 'gram',
            'base_unit' => 'kg',
            'price_per_unit' => 17.0,
            'price_per_base_unit' => 17000.0,
            'current_stock' => 5000,
            'min_stock_level' => 1000,
        ]);

        $result = $this->stockService->reduceStock([
            'material_id' => $material->id,
            'amount' => 1000,
            'description' => 'Produksi Kue Tart',
        ]);

        $this->assertEquals(4000, $result->current_stock);

        // Assert stock log created
        $this->assertDatabaseHas('stock_logs', [
            'material_id' => $material->id,
            'type' => StockLogType::OUT->value,
            'amount' => 1000,
            'description' => '[MANUAL] Produksi Kue Tart',
        ]);
    }

    /**
     * Test mengurangi stok gagal karena stok tidak cukup
     */
    public function test_reduce_stock_fails_when_insufficient(): void
    {
        $material = Material::create([
            'name' => 'Minyak Goreng',
            'unit' => 'gram',
            'base_unit' => 'kg',
            'price_per_unit' => 17.0,
            'price_per_base_unit' => 17000.0,
            'current_stock' => 500,
            'min_stock_level' => 100,
        ]);

        $this->expectException(InsufficientStockException::class);

        $this->stockService->reduceStock([
            'material_id' => $material->id,
            'amount' => 1000,
            'description' => 'Test reduction',
        ]);
    }

    /**
     * Test mengurangi stok sama dengan stok yang ada (habis)
     */
    public function test_reduce_stock_exact_amount(): void
    {
        $material = Material::create([
            'name' => 'SP Emulsifier',
            'unit' => 'gram',
            'base_unit' => 'kg',
            'price_per_unit' => 80.0,
            'price_per_base_unit' => 80000.0,
            'current_stock' => 300,
            'min_stock_level' => 50,
        ]);

        $result = $this->stockService->reduceStock([
            'material_id' => $material->id,
            'amount' => 300,
            'description' => 'Produksi',
        ]);

        $this->assertEquals(0, $result->current_stock);
    }

    /**
     * Test mengurangi stok dengan amount 0
     */
    public function test_reduce_stock_with_zero_amount(): void
    {
        $material = Material::create([
            'name' => 'Garam',
            'unit' => 'gram',
            'base_unit' => 'kg',
            'price_per_unit' => 7.0,
            'price_per_base_unit' => 7000.0,
            'current_stock' => 500,
            'min_stock_level' => 100,
        ]);

        $result = $this->stockService->reduceStock([
            'material_id' => $material->id,
            'amount' => 0,
            'description' => 'Test zero reduction',
        ]);

        $this->assertEquals(500, $result->current_stock);
    }

    /**
     * Test multiple add stock operations
     */
    public function test_multiple_add_stock_operations(): void
    {
        $material = Material::create([
            'name' => 'Tepung Maizena',
            'unit' => 'gram',
            'base_unit' => 'kg',
            'price_per_unit' => 21.0,
            'price_per_base_unit' => 21000.0,
            'current_stock' => 0,
            'min_stock_level' => 100,
        ]);

        // Add stock 3 times
        $this->stockService->addStock([
            'material_id' => $material->id,
            'amount' => 200,
            'description' => 'Pembelian 1',
        ]);

        $this->stockService->addStock([
            'material_id' => $material->id,
            'amount' => 300,
            'description' => 'Pembelian 2',
        ]);

        $this->stockService->addStock([
            'material_id' => $material->id,
            'amount' => 500,
            'description' => 'Pembelian 3',
        ]);

        $material->refresh();

        $this->assertEquals(1000, $material->current_stock);
        $this->assertEquals(3, StockLog::where('material_id', $material->id)->count());
    }

    /**
     * Test stock log created with correct type for add
     */
    public function test_stock_log_type_in_for_add(): void
    {
        $material = Material::create([
            'name' => 'Pewarna Pandan',
            'unit' => 'gram',
            'base_unit' => 'kg',
            'price_per_unit' => 110.0,
            'price_per_base_unit' => 110000.0,
            'current_stock' => 100,
            'min_stock_level' => 50,
        ]);

        $this->stockService->addStock([
            'material_id' => $material->id,
            'amount' => 50,
            'description' => 'Test',
        ]);

        $log = StockLog::where('material_id', $material->id)->first();
        $this->assertEquals(StockLogType::IN, $log->type);
    }

    /**
     * Test stock log created with correct type for reduce
     */
    public function test_stock_log_type_out_for_reduce(): void
    {
        $material = Material::create([
            'name' => 'Butter Cream',
            'unit' => 'gram',
            'base_unit' => 'kg',
            'price_per_unit' => 28.0,
            'price_per_base_unit' => 28000.0,
            'current_stock' => 2000,
            'min_stock_level' => 500,
        ]);

        $this->stockService->reduceStock([
            'material_id' => $material->id,
            'amount' => 500,
            'description' => 'Test',
        ]);

        $log = StockLog::where('material_id', $material->id)->first();
        $this->assertEquals(StockLogType::OUT, $log->type);
    }

    /**
     * Test reduce stock with material not found
     */
    public function test_reduce_stock_material_not_found(): void
    {
        $this->expectException(\Illuminate\Database\Eloquent\ModelNotFoundException::class);

        $this->stockService->reduceStock([
            'material_id' => 99999,
            'amount' => 100,
            'description' => 'Test',
        ]);
    }

    /**
     * Test add stock with invalid material id throws exception
     */
    public function test_add_stock_invalid_material(): void
    {
        $this->expectException(\Illuminate\Database\QueryException::class);

        $this->stockService->addStock([
            'material_id' => 99999,
            'amount' => 100,
            'description' => 'Test',
        ]);
    }
}
