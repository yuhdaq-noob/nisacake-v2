<?php

namespace App\Http\Controllers;

use App\Exceptions\InsufficientStockException;
use App\Http\Requests\ReduceStockRequest;
use App\Http\Requests\UpdateMaterialPriceRequest;
use App\Http\Resources\MaterialResource;
use App\Models\Material;
use App\Models\MaterialPriceLog;
use App\Services\StockService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;


// Controller untuk manajemen bahan baku (material)
class MaterialController extends Controller
{
    // Injeksi service stok ke controller
    public function __construct(
        private StockService $stockService
    ) {}

    // Mengambil semua bahan baku
    // FIXME: bisa pindah ke servi
    public function index(): AnonymousResourceCollection
    {
        $materials = Material::with('stockLogs')
            ->orderBy('current_stock', 'asc')
            ->get();
        // Kembalikan dalam bentuk resource collection
        return MaterialResource::collection($materials);
    }

    // Mengurangi stok material dengan validasi dan pencatatan penyesuaian
    public function reduceStock(ReduceStockRequest $request): JsonResponse
    {
        try {
            // Kurangi stok via service
            $material = $this->stockService->reduceStock($request->validated());

            // Kembalikan response sukses
            return response()->json([
                'status' => 'success',
                'message' => 'Stok berhasil dikurangi & dicatat.',
                'data' => new MaterialResource($material),
            ], 200);

        } catch (InsufficientStockException $e) {
            // Jika stok kurang, kembalikan error 400
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], 400);

        } catch (\Exception $e) {
            // Jika error lain, log dan kembalikan error 500
            Log::error('Gagal mengurangi stok: '.$e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal mengurangi stok.',
            ], 500);
        }
    }

    // FIXME: sebaiknya pindah ke MODEL
    // MengUpdate harga material per base unit dan sinkron harga per unit kecil
    public function updatePrice(UpdateMaterialPriceRequest $request, Material $material): JsonResponse
    {
        // Normalisasi satuan dan mapping konversi seharusnya ada di model
        $unit = strtolower(trim((string) $material->unit));
        $conversionMap = [
            'gram' => ['base_unit' => 'kg', 'factor' => 1000],
            'g' => ['base_unit' => 'kg', 'factor' => 1000],
            'ml' => ['base_unit' => 'liter', 'factor' => 1000],
            'pcs' => ['base_unit' => 'Pack', 'factor' => 100],
        ];

        $pricePerBaseUnit = (float) $request->validated()['price_per_base_unit']; // Harga per base unit baru
        $oldPricePerUnit = $material->price_per_unit;
        $oldPricePerBaseUnit = $material->price_per_base_unit;

        if (array_key_exists($unit, $conversionMap)) {
            $baseUnit = $conversionMap[$unit]['base_unit'];
            $factor = $conversionMap[$unit]['factor'];
            // Hitung harga per unit kecil dari harga per base unit
            $pricePerUnit = $pricePerBaseUnit / $factor;
        } else {
            $baseUnit = $material->unit;
            $pricePerUnit = $pricePerBaseUnit;
        }

        // Update data material
        $material->base_unit = $baseUnit;
        $material->price_per_base_unit = $pricePerBaseUnit;
        $material->price_per_unit = $pricePerUnit;
        $material->save();

        // Catat histori perubahan harga
        MaterialPriceLog::create([
            'material_id' => $material->id,
            'user_id' => Auth::check() ? Auth::id() : null,
            'old_price_per_unit' => $oldPricePerUnit,
            'new_price_per_unit' => $material->price_per_unit,
            'old_price_per_base_unit' => $oldPricePerBaseUnit,
            'new_price_per_base_unit' => $material->price_per_base_unit,
            'base_unit' => $material->base_unit,
        ]);

        // Kembalikan response sukses
        return response()->json([
            'status' => 'success',
            'message' => 'Harga berhasil diperbarui.',
            'data' => new MaterialResource($material),
        ], 200);
    }
}
