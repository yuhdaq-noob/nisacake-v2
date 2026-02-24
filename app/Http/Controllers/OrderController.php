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

    // Mengambil daftar pre-order yang belum diproses
    public function getScheduledOrders(): AnonymousResourceCollection
    {
        $preOrders = Order::where('status', OrderStatus::PRE_ORDER->value)
            ->with('items.product')
            ->orderBy('scheduled_at', 'asc')
            ->get();

        return OrderResource::collection($preOrders);
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

    // Membatalkan pre-order
    public function cancel(Order $order): JsonResponse
    {
        // Validasi bahwa order yang diakses adalah pre-order
        if ($order->status !== OrderStatus::PRE_ORDER) {
            return response()->json([
                'status' => 'error',
                'message' => 'Hanya pesanan terjadwal yang dapat dibatalkan.',
            ], 400);
        }

        $order->status = OrderStatus::CANCELLED->value;
        $order->save();

        return response()->json([
            'status' => 'success',
            'message' => 'Pesanan berhasil dibatalkan.',
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

    // Membuat pre-order tanpa mengurangi stok (penjadwalan pesanan)
    public function preOrder(StoreOrderRequest $request): JsonResponse
    {
        try {
            // Proses pembuatan pre-order via service
            $order = $this->orderService->createPreOrder($request->validated());
            return response()->json([
                'status' => 'success',
                'message' => 'Pre-order berhasil dijadwalkan.',
                'data' => new OrderResource($order->load('items.product')),
            ], 201);

        } catch (MaterialNotFoundException $e) {
            // Jika produk tidak ditemukan, kembalikan error 404
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], 404);

        } catch (\Exception $e) {
            // Jika error lain, log dan kembalikan error 500
            Log::error('Gagal membuat pre-order: '.$e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal membuat pre-order.',
            ], 500);
        }
    }

    // Mengeksekusi pre-order: konversi dari PRE_ORDER ke COMPLETED dengan deduction stok
    public function executePreOrder(Order $order): JsonResponse
    {
        try {
            // Validasi bahwa order yang diakses adalah pre-order
            // Karena status di-cast ke OrderStatus enum, bandingkan dengan enum, bukan string
            if ($order->status !== OrderStatus::PRE_ORDER) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Order ini bukan pre-order atau sudah dieksekusi.',
                ], 400);
            }

            // Load items relationship untuk executePreOrder
            if (!$order->relationLoaded('items')) {
                $order->load('items');
            }

            // Eksekusi pre-order dengan deduction stok
            $completedOrder = $this->orderService->executePreOrder($order);

            return response()->json([
                'status' => 'success',
                'message' => 'Pre-order berhasil dieksekusi.',
                'data' => new OrderResource($completedOrder->load('items.product')),
            ], 200);

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

        } catch (\InvalidArgumentException $e) {
            // Jika argument invalid
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], 400);

        } catch (\Exception $e) {
            // Jika error lain, log dan kembalikan error 500
            Log::error('Gagal mengeksekusi pre-order: '.$e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal mengeksekusi pre-order.',
            ], 500);
        }
    }
}
