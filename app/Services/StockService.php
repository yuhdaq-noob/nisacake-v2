<?php

namespace App\Services;

use App\Enums\StockLogType;
use App\Exceptions\InsufficientStockException;
use App\Models\Material;
use App\Models\StockLog;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class StockService
{
    /**
     * Tambah stok ke material
     */
    public function addStock(array $data): Material
    {
        return DB::transaction(function () use ($data) {
            // Buat log stok
            StockLog::create([
                'material_id' => $data['material_id'],
                'type' => StockLogType::IN->value,
                'amount' => $data['amount'],
                'description' => $data['description'],
            ]);

            // Tambah stok
            $material = Material::findOrFail($data['material_id']);
            $material->increment('current_stock', $data['amount']);

            Log::channel('business')->info('Stock added', [
                'material_id' => $material->id,
                'material_name' => $material->name,
                'amount' => $data['amount'],
                'description' => $data['description'],
                'current_stock' => $material->current_stock,
            ]);

            return $material;
        });
    }

    /**
     * Kurangi stok material dengan penguncian pesimis untuk mencegah race condition
     *
     * @throws InsufficientStockException
     */
    public function reduceStock(array $data): Material
    {
        return DB::transaction(function () use ($data) {
            // Kunci baris material untuk update agar tidak terjadi kondisi balapan
            $material = Material::where('id', $data['material_id'])
                ->lockForUpdate()
                ->firstOrFail();

            // Periksa apakah stok mencukupi (dalam transaksi dengan kunci)
            if ($material->current_stock < $data['amount']) {
                throw new InsufficientStockException(
                    $material->name,
                    $material->current_stock,
                    $data['amount']
                );
            }

            // Kurangi stok
            $material->decrement('current_stock', $data['amount']);

            // Buat log stok
            StockLog::create([
                'material_id' => $material->id,
                'type' => StockLogType::OUT->value,
                'amount' => $data['amount'],
                'description' => '[MANUAL] '.$data['description'],
            ]);

            Log::channel('business')->info('Stock reduced', [
                'material_id' => $material->id,
                'material_name' => $material->name,
                'amount' => $data['amount'],
                'description' => $data['description'],
                'current_stock' => $material->current_stock,
            ]);

            return $material;
        });
    }
}
