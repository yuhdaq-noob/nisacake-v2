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

class OrderController extends Controller
{
    public function __construct(private OrderService $orderService) {}

    public function index(): AnonymousResourceCollection
    {
        $orders = Order::with('items.product')
            ->orderBy('created_at', 'desc')
            ->get();
        return OrderResource::collection($orders);
    }

    public function getScheduledOrders(): AnonymousResourceCollection
    {
        $preOrders = Order::where('status', OrderStatus::PRE_ORDER->value)
            ->with('items.product')
            ->orderBy('scheduled_at', 'asc')
            ->get();
        return OrderResource::collection($preOrders);
    }

    public function show(Order $order): OrderResource
    {
        return new OrderResource($order->load('items.product'));
    }

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

    public function cancel(Order $order): JsonResponse
    {
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

    public function store(StoreOrderRequest $request): JsonResponse
    {
        try {
            $order = $this->orderService->createOrder($request->validated());
            return response()->json([
                'status' => 'success',
                'message' => 'Order berhasil diproses.',
                'data' => new OrderResource($order->load('items.product')),
            ], 201);
        } catch (InsufficientStockException $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], 400);
        } catch (MaterialNotFoundException $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], 404);
        } catch (\Exception $e) {
            Log::error('Gagal membuat order: '.$e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal membuat order.',
            ], 500);
        }
    }

    public function preOrder(StoreOrderRequest $request): JsonResponse
    {
        try {
            $order = $this->orderService->createPreOrder($request->validated());
            return response()->json([
                'status' => 'success',
                'message' => 'Pre-order berhasil dijadwalkan.',
                'data' => new OrderResource($order->load('items.product')),
            ], 201);
        } catch (MaterialNotFoundException $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], 404);
        } catch (\Exception $e) {
            Log::error('Gagal membuat pre-order: '.$e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal membuat pre-order.',
            ], 500);
        }
    }

    public function executePreOrder(Order $order): JsonResponse
    {
        try {
            if ($order->status !== OrderStatus::PRE_ORDER) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Order ini bukan pre-order atau sudah dieksekusi.',
                ], 400);
            }
            if (!$order->relationLoaded('items')) {
                $order->load('items');
            }
            $completedOrder = $this->orderService->executePreOrder($order);
            return response()->json([
                'status' => 'success',
                'message' => 'Pre-order berhasil dieksekusi.',
                'data' => new OrderResource($completedOrder->load('items.product')),
            ], 200);
        } catch (InsufficientStockException $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], 400);
        } catch (MaterialNotFoundException $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], 404);
        } catch (\InvalidArgumentException $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], 400);
        } catch (\Exception $e) {
            Log::error('Gagal mengeksekusi pre-order: '.$e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal mengeksekusi pre-order.',
            ], 500);
        }
    }
}
