<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreStockRequest;
use App\Http\Resources\StockLogResource;
use App\Models\StockLog;
use App\Services\StockService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Log;

class StockController extends Controller
{
    public function __construct(private StockService $stockService) {}

    public function store(StoreStockRequest $request): JsonResponse
    {
        try {
            $material = $this->stockService->addStock($request->validated());
            return response()->json([
                'status' => 'success',
                'message' => 'Stok berhasil ditambah.',
                'data' => [
                    'material_id' => $material->id,
                    'current_stock' => $material->current_stock,
                ],
            ], 201);
        } catch (\Exception $e) {
            Log::error('Gagal menambah stok: '.$e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal menambah stok.',
            ], 500);
        }
    }

    public function index(): AnonymousResourceCollection
    {
        $logs = StockLog::with('material')
            ->orderBy('created_at', 'desc')
            ->take(50)
            ->get();
        return StockLogResource::collection($logs);
    }
}
