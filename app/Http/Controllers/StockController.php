<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreStockRequest;
use App\Http\Resources\StockLogResource;
use App\Models\StockLog;
use App\Services\StockService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Log;


// Controller untuk manajemen stok bahan baku
class StockController extends Controller
{
    // Injeksi service stok ke controller
    public function __construct(
        private StockService $stockService
    ) {}

    // Menambah stok baru ke bahan baku
    public function store(StoreStockRequest $request): JsonResponse
    {
        try {
            // Tambah stok via service
            $material = $this->stockService->addStock($request->validated());

            // Kembalikan response sukses beserta stok terbaru
            return response()->json([
                'status' => 'success',
                'message' => 'Stok berhasil ditambah.',
                'data' => [
                    'material_id' => $material->id,
                    'current_stock' => $material->current_stock,
                ],
            ], 201);

        } catch (\Exception $e) {
            // Jika gagal, log error dan kembalikan response error
            Log::error('Gagal menambah stok: '.$e->getMessage());

            return response()->json([
                'status' => 'error',
                'message' => 'Gagal menambah stok.',
            ], 500);
        }
    }

    // Mengambil histori log stok (terbaru 50 data)
    public function index(): AnonymousResourceCollection
    {
        $logs = StockLog::with('material')
            ->orderBy('created_at', 'desc')
            ->take(50)
            ->get();

        // Kembalikan dalam bentuk resource collection
        return StockLogResource::collection($logs);
    }
}
