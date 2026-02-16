<?php

namespace App\Http\Controllers;

use App\Enums\OrderStatus;
use App\Exceptions\InsufficientStockException;
use App\Exceptions\MaterialNotFoundException;
use App\Http\Requests\StoreOrderRequest;
use App\Http\Resources\OrderResource;
use App\Models\Order;
use App\Services\OrderService;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;


// Controller untuk manajemen order/pesanan
class OrderController extends Controller
{
    // Injeksi service order ke controller
    public function __construct(
        private OrderService $orderService
    ) {}

    // Mengambil semua order beserta item dan produk
    public function index(): AnonymousResourceCollection
    {
        $orders = Order::with('items.product')
            ->orderBy('created_at', 'desc')
            ->get();
        // Kembalikan dalam bentuk resource collection
        return OrderResource::collection($orders);
    }

    // Menampilkan detail satu order beserta item dan produk
    public function show(Order $order): OrderResource
    {
        return new OrderResource($order->load('items.product'));
    }

    // Menandai order sebagai selesai
    public function complete(Order $order): JsonResponse
    {
        $order->status = OrderStatus::COMPLETED->value;
        $order->save();
        return response()->json([
            'status' => 'success',
            'message' => 'Order berhasil diselesaikan.',
            'data' => new OrderResource($order->load('items.product')),
        ]);
    }

    // Membuat order baru
    public function store(StoreOrderRequest $request): JsonResponse
    {
        try {
            // Proses pembuatan order via service
            $order = $this->orderService->createOrder($request->validated());
            return response()->json([
                'status' => 'success',
                'message' => 'Order berhasil diproses.',
                'data' => new OrderResource($order->load('items.product')),
            ], 201);

        } catch (InsufficientStockException $e) {
            // Jika stok kurang, kembalikan error 400
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], 400);

        } catch (MaterialNotFoundException $e) {
            // Jika bahan tidak ditemukan, kembalikan error 404
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], 404);

        } catch (\Exception $e) {
            // Jika error lain, log dan kembalikan error 500
            Log::error('Gagal membuat order: '.$e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal membuat order.',
            ], 500);
        }
    }
}
